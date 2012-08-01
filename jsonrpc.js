/**
 * JSON RPC
 * 
 * @link https://github.com/cakyus/jsonrpc
 * @example
 * var rpc = new jsonrpc('http://localhost/rpc.php');
 * rpc.call('methodName', [], function(result) {
 *     // do something
 *     });
 **/

function jsonrpc(url) {
	
	this.url = url;

	this.call = function(method, params, callback) {
		
		var data = {
			 'jsonrpc'	: '2.0'
			,'method'	: method
			,'params'	: params
			,'id'		: this._getUniqueId()
			};
		
		this._ajaxRequestSend('POST', this.url, data, callback);
	}
	
	this._getUniqueId = function() {
		return Math.random().toString().substring(2);
	}
	
	this._ajaxRequestSend = function(method, url, data, callback) {
		
		var xmlhttp = new XMLHttpRequest;
		
		xmlhttp.onreadystatechange = function() {
			if(xmlhttp.readyState == 4){
				callback(JSON.parse(xmlhttp.responseText));
			}
		};
		
		xmlhttp.open(method, url, true);
		xmlhttp.send(JSON.stringify(data));	
	}	
}