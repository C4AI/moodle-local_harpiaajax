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
 *             provider_hash: "hash of the answer provider",
 *         },
 *     }])
 *
 * @package    local_harpiaajax
 * @copyright  2024 C4AI - USP
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class send_message extends external_api
{


    public static function execute_parameters()
    {
        // Definition of the input parameters.

        return new external_function_parameters(
            array(
                "query" => new external_value(PARAM_TEXT, "User query"),
                "provider_hash" => new external_value(PARAM_TEXT, "Hash of answer provider"),
                "history" =>
                    new external_multiple_structure(
                        new external_value(PARAM_TEXT, "Message history")
                    ),
                "harpia_data_field_id" => new external_value(PARAM_INT, "Database field id", VALUE_DEFAULT, -1),
            ),
        );
    }


    public static function execute($query, $provider_hash, $history, $harpia_data_field_id)
    {
        // Implementation of the service.

        global $DB, $CFG;

        $params = self::validate_parameters(
            self::execute_parameters(),
            array(
                'query' => $query,
                'provider_hash' => $provider_hash,
                'history' => $history,
                'harpia_data_field_id' => $harpia_data_field_id,
            )
        );

        $providers = self::fetch_providers()->providers;
        $chosen_provider = null;
        $system_prompt = "";
        foreach ($providers as $provider_item) {
            $provider = $provider_item->name;
            if (password_verify($provider, $provider_hash)) {
                $chosen_provider = $provider;
                $system_prompt = $provider_item->default_system_prompt;
                break;
            }
        }
        if ($chosen_provider === null)
            return null;


        if ($harpia_data_field_id >= 0) {
            $where = ['id' => $harpia_data_field_id];
            $prompt = $DB->get_field('data_fields', 'param3', $where);
            if ($prompt !== null and $prompt !== '') {
                $system_prompt = $prompt;
            }
        }


        $result = self::send_a_message(
            $query,
            $chosen_provider,
            $history,
            $system_prompt
        );

        return array(
            "output" => array(
                "answer" => $result->text,
                "contexts" => array(
                )
            )
        );
    }


    public static function execute_returns()
    {
        // Definition of the output format.

        return new external_single_structure([
            'output' => new external_single_structure([
                'answer' => new external_value(PARAM_TEXT, 'The answer text'),
                'contexts' => new external_multiple_structure(
                    new external_single_structure(['text' => new external_value(PARAM_TEXT, 'The text in the context')]),
                )
            ])
        ]);
    }


    // The two functions below interact with the external answer providers
    //   (implemented in Python - see the directory "harpia_answer_providers").

    /** 
     * Send a message to the language model and return its output.
     * 
     * @param string $query     the user query
     * @param string $provider  the hash associated to the provider
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