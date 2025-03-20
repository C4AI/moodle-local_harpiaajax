<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <https://www.gnu.org/licenses/>.


require_once($CFG->libdir . "/externallib.php");


/**
 * Web service that implements an interaction with an external language model. 
 *
 * It can be accessed by Moodle plugins through Javascript, as in this example:
 * 
 *     ajax.call([{
 *         methodname: "local_harpiaajax_send_message",
 *         args: {
 *             query: "user query here",
 *         },
 *     }])
 *
 * @package    local_harpiaajax
 * @copyright  2025 C4AI - USP
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class send_message extends external_api
{


    public static function execute_to_datafield_harpiainteraction_parameters()
    {
        // Definition of the parameters for the function execute_to_datafield_harpiainteraction().

        return new external_function_parameters(
            [
                "query" => new external_value(PARAM_TEXT, "User query"),
                "field_id" => new external_value(PARAM_INT, "Database field id", VALUE_DEFAULT, -1),
            ],
        );
    }

    public static function execute_to_datafield_harpiainteraction($query, $field_id)
    {
        // Implementation of the service.

        global $DB, $CFG;


        // Validate the input parameters..
        self::validate_parameters(
            self::execute_to_datafield_harpiainteraction_parameters(),
            [
                'query' => $query,
                'field_id' => $field_id,
            ]
        );

        $system_prompt = "";
        $where = ['id' => $field_id];
        $d = $DB->get_field('data_fields', 'dataid', $where);
        $system_prompt = $DB->get_field('data_fields', 'param3', $where);
        $answer_provider = $DB->get_field('data_fields', 'param1', $where);
        // TODO: check permission  

        $result = self::send_a_message(
            $query,
            $answer_provider,
            [], // TODO: implement history
            $system_prompt ?? ""
        );

        $now = time();
        $interaction_id = $DB->insert_record('data_harpiainteraction', [
            'timestamp' => $now,
            'userid' => 0,
            'dataid' => $d,
            'parentdataid' => null,
            'answer_provider' => $answer_provider,
            'query' => $query,
            'system_prompt' => $system_prompt,
            'answer' => $result->text,
        ]);

        return [
            "output" => [
                "answer" => $result->text,
                "contexts" => [],
                "interaction_id" => $interaction_id,
            ]
        ];
    }


    public static function execute_to_datafield_harpiainteraction_returns()
    {
        // Definition of the output format.

        return new external_single_structure([
            'output' => new external_single_structure([
                'answer' => new external_value(PARAM_TEXT, 'The answer text'),
                'contexts' => new external_multiple_structure(
                    new external_single_structure(['text' => new external_value(PARAM_TEXT, 'The text in the context')]),
                ),
                'interaction_id' => new external_value(PARAM_INT, 'The id of the interaction')
            ])
        ]);
    }


    // The two functions below interact with the external answer providers
    //   (implemented in Python - see the directory "harpia_answer_providers").

    /** 
     * Send a message to the language model and return its output.
     * 
     * @param string $query         the user query
     * @param string $provider      the provider name
     * @param array  $history       the previous messages (user, model, user, model...)
     * @param string $system_prompt the system prompt, if any
     * 
     * @return array the generated output
     */
    private static function send_a_message($query, $provider, $history, $system_prompt)
    {
        $data = array(
            'query' => $query,
            'answer_provider' => $provider,
            'history' => $history,
            'system_prompt' => $system_prompt,
        );
        $address = rtrim(get_config('local_harpiaajax', 'answerprovideraddress'), '/');
        $url = $address . '/send';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $error = curl_errno($ch);
        if ($error) {
            error_log(`CURL error: $error`);
        }
        curl_close($ch);

        return json_decode($response);
    }

    /** 
     * Obtain the list of answer providers.
     * 
     * @return array list of current answer providers
     */
    public static function fetch_providers()
    {
        $address = rtrim(get_config('local_harpiaajax', 'answerprovideraddress'), '/');
        $url = $address . '/list';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $error = curl_errno($ch);
        if ($error) {
            error_log(`CURL error: $error`);
        }
        curl_close($ch);

        return json_decode($response);
    }


}