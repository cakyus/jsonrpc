JSONRPC
=======

About
-----
Simple implementation of JSON-RPC in Javascript and PHP

Example
-------
```javascript
// call method "jsonRpcSum" in the server and return the result

var rpc = new jsonrpc('http://127.0.0.1/jsonrpc.php');
rpc.call('jsonRpcSum', [1,2,3], function(response) {
	alert(response.result); // 6
});
```
