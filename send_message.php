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
                "query" => new external_value(PARAM_TEXT, "User query", VALUE_REQUIRED, null, NULL_NOT_ALLOWED),
                "field_id" => new external_value(PARAM_INT, "Database field id", VALUE_REQUIRED, null, NULL_NOT_ALLOWED),
                "parent_rid" => new external_value(PARAM_INT, "Database field id", VALUE_DEFAULT, null, NULL_ALLOWED),
            ],
        );
    }

    public static function execute_to_datafield_harpiainteraction($query, $field_id, $parent_rid = null)
    {
        // Implementation of the service.

        global $DB, $CFG, $USER;


        // Validate the input parameters..
        self::validate_parameters(
            self::execute_to_datafield_harpiainteraction_parameters(),
            [
                'query' => $query,
                'field_id' => $field_id,
                'parent_rid' => $parent_rid,
            ]
        );

        $system_prompt = "";
        $record = $DB->get_record('data_fields', ['id' => $field_id]);
        $d = $record->dataid;
        $system_prompt = $record->param3;
        $answer_provider = $record->param1;

        $history = [];
        if ($parent_rid) {
            $where = ['fieldid' => $field_id, 'recordid' => $parent_rid];
            $history = json_decode($DB->get_field('data_content', 'content2', $where) ?: '[]');
        }

        // TODO: check permission  

        $result = self::send_a_message(
            $query,
            $answer_provider,
            $history,
            $system_prompt ?? ""
        );

        $now = time();
        $interaction_id = $DB->insert_record('data_harpiainteraction', [
            'timestamp' => $now,
            'userid' => $USER->id,
            'dataid' => $d,
            'recordid' => null,
            'parentrecordid' => $parent_rid,
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