<?php

/**
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 **/

include('config.php');

/**
 * JSON RPC
 *
 * @link https://github.com/cakyus/jsonrpc
 **/

$rpcServer = new JsonRpcServer;
$rpcServer->write();

/**
 * JSON RPC Server
 *
 * @link http://www.jsonrpc.org/specification
 **/

class JsonRpcServer {

	private $id = null;

	const ERROR_PARSE 				= -32700;
	const ERROR_INVALID_REQUEST 	= -32600;
	const ERROR_METHOD_NOT_FOUND 	= -32601;
	const ERROR_INVALID_PARAMS 		= -32602;
	const ERROR_INTERNAL_ERROR 		= -32603;
	const ERROR_SERVER_ERROR 		= -32000;

	public function getResponseError($error, $data='') {

		switch ($error) {
			case self::ERROR_PARSE:
				$message = 'Parse error';
				break;
			case self::ERROR_INVALID_REQUEST:
				$message = 'Invalid request';
				break;
			case self::ERROR_METHOD_NOT_FOUND:
				$message = 'Method not found';
				break;
			case self::ERROR_INVALID_PARAMS:
				$message = 'Invalid parameters';
				break;
			case self::ERROR_INTERNAL_ERROR:
				$message = 'Internal error';
				break;
			case self::ERROR_SERVER_ERROR:
				$message = 'Server error';
				break;
			default:
				$message = 'Server error';
		}

		return array(
			 'jsonrpc' => '2.0'
			,'error' => array(
				 'code' => $error
				,'message' => $message
				,'data' => $data
				)
			,'id' => $this->id
			);
	}

	public function getResponseResult($result) {
		return array(
			 'jsonrpc' => '2.0'
			,'result' => $result
			,'id' => $this->id
			);
	}

	public function getResponse() {

		$input = file_get_contents('php://input');
		if (!$request = json_decode($input)) {
			return $this->getResponseError(self::ERROR_PARSE);
		}

		if (property_exists($request, 'id') == false){
			$this->id = null;
		} else {
			$this->id = $request->id;
		}

		if (property_exists($request, 'params') == false){
			$request->params = array();
		}

		include('functions.php');

		if (function_exists($request->method)) {

			try {

				$responseResult = call_user_func_array($request->method, $request->params);
				$responseError = error_get_last();

				if (is_null($responseError)){
					return $this->getResponseResult($responseResult);
				}

				return $this->getResponseError(self::ERROR_SERVER_ERROR, $responseError['message']);

			} catch (\Exception $e){
				return $this->getResponseError(self::ERROR_SERVER_ERROR, $e->getMessage());
			}
		}

		return $this->getResponseError(self::ERROR_METHOD_NOT_FOUND);
	}
	public function write() {
		header('Content-Type: application/json');
		echo json_encode($this->getResponse());
	}
}

// test function which will be called

function jsonRpcSum() {
	$params = func_get_args();
	$result = 0;
	foreach ($params as $param) {
		$result += $param;
	}
	return $result;
}

