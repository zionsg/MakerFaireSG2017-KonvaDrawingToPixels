<?php

namespace App;

/**
 * @api {post} /app Submit drawing
 *
 * @apiParam {array} cells Array of color codes for each cell, row by row
 * @apiSuccessExample {json} Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *       "grid": [
 *         ["#ff0000", "#00ff00"],
 *         ["#0000ff", "#ffffff"]
 *       ],
 *       "api_call": {
 *         "code": 200,
 *         "response": "OK"
 *       }
 *     }
 * @see http://apidocjs.com
 */
class Application
{
    protected $endpointUrl;
    protected $cellsPerRow;
    protected $cellsPerColumn;

    public function __construct(array $config)
    {
        $this->endpointUrl = $config['endpoint_url'];
        $this->cellsPerRow = $config['cells_per_row'];
        $this->cellsPerColumn = $config['cells_per_column'];
    }

    /**
     * Parse image data uri, resize image and return grid information
     *
     * @return void JSON-encoded string will be echoed as per @apiSuccessExample
     */
    public function run()
    {
        // Get colors of cells from POST request param
        $cells = isset($_POST['cells']) ? $_POST['cells'] : [];

        if (! $cells || ! is_array($cells)) {
            return $this->response([
                'grid' => [],
            ]);
        }

        // Sending to external API column by column, with even columns reversed
        // LED board is wired as follows for a 3 x 2 grid:
        //     01  04  05
        //     02  03  06
        $grid = [];
        for ($col = 0; $col < $this->cellsPerRow; $col++) {
            $colInfo = [];

            for ($row = 0; $row < $this->cellsPerColumn; $row++) {
                $cell = $cells[$row][$col];

                if (0 === ($col % 2)) {
                    $colInfo[] = $cell;
                } else {
                    array_unshift($colInfo, $cell);
                }
            }

            $grid[] = implode(',', $colInfo);
        }

        // Send data to external API
        $data = implode(',', $grid);
        $apiCall = $this->call($this->endpointUrl, ['data' => $data]);

        return $this->response([
            'grid' => $grid,
            'api_call' => $apiCall,
        ]);
    }

    /**
     * Convert decimal to 2-digit hex
     *
     * @param  int $decimal
     * @return string
     */
    protected function decToHex($decimal)
    {
        $hex = dechex($decimal);

        return str_pad($hex, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Send cURL request to external API
     *
     * @param  string $url
     * @param  array $data
     * @return array ['code' => <HTTP response code>, 'response' => <response data>]
     */
    protected function call($url, array $data)
    {
        if (! $url) {
            return [
                'code' => null,
                'response' => null,
            ];
        }

        $headers = ['Content-Type: application/x-www-form-urlencoded; charset=utf-8'];
        $postDataStr = '';
        foreach ($data as $key => $value) {
            $postDataStr .= "${key}=${value}";
        }

        $curlHandler = curl_init();
        curl_setopt_array($curlHandler, [
            CURLOPT_RETURNTRANSFER => true, // return value instead of output to browser
            CURLOPT_HEADER => false, // do not include headers in return value
            CURLOPT_USERAGENT => $_SERVER['HTTP_USER_AGENT'], // some servers reject requests with no user agent
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $postDataStr,
        ]);
        $apiResponse = curl_exec($curlHandler);
        $curlInfo = curl_getinfo($curlHandler);
        $apiCode = $curlInfo['http_code'];
        curl_close($curlHandler);

        return [
            'code' => $apiCode,
            'response' => $apiResponse,
        ];
    }

    /**
     * Return JSON response
     *
     * @param  array $data
     * @return void
     */
    protected function response(array $data, $responseCode = 200)
    {
        $response = json_encode($data);
        header_remove();
        http_response_code($responseCode);
        header('Content-Type: application/json; charset=utf8');
        echo $response;
        exit;
    }
}
