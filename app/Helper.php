<?PHP

function findRoot(){
	if(!preg_match("/^https?\:\/\/[^\/]*(\/.*?)$/", $_ENV["APP_URL"], $matches)){ 
		return "Malformed APP_URL environment variable.";
	}
	$root=$matches[1];
	if($root=='/'){
		$root='';
	}
	return $root;
}
