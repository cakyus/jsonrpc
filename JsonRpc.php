<?php
/**
 * JSON RPC
 * 
 * @link https://github.com/cakyus/jsonrpc
 * @link http://www.jsonrpc.org/specification
 **/

namespace JsonRpc;

class JsonRpc {
	
	private $id = null;
	
	const ERROR_PARSE 				= -32700;
	const ERROR_INVALID_REQUEST 	= -32600;
	const ERROR_METHOD_NOT_FOUND 	= -32601;
	const ERROR_INVALID_PARAMS 	= -32602;
	const ERROR_INTERNAL_ERROR 	= -32603;
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
		
		$this->id = $request->id;
		
		if (function_exists($request->method)) {
			return $this->getResponseResult(
				call_user_func_array($request->method, $request->params)
			);
		}
		
		$callbacks = explode('\\', $request->method);
		
		if (count($callbacks) == 1) {
			return $this->getResponseError(self::ERROR_METHOD_NOT_FOUND);
		}
		
		$function = array_pop($callbacks);
		$class = implode('\\', $callbacks);
		
		if (!class_exists($class)) {
			return $this->getResponseError(self::ERROR_METHOD_NOT_FOUND);
		}
		
		$object = new $class;
		
		if (!method_exists($object, $function)) {
			return $this->getResponseError(self::ERROR_METHOD_NOT_FOUND);
		}
		
		return $this->getResponseResult(
			call_user_func_array(array($object, $function), $request->params)
		);
	}
	
	public function write() {
		header('Content-Type: application/json');
		echo json_encode($this->getResponse());
	}
}

