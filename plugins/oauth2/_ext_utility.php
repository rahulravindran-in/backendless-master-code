<?php

require_once __DIR__.'/../../../../plugins/<projectHash>/oauth2/server.php';

function enable_oauth2($scopeRequired){
   global $server;
   $request = OAuth2\Request::createFromGlobals();
   $response = new OAuth2\Response();
   if (!$server->verifyResourceRequest($request, $response, $scopeRequired)) {
       $response->send();
       die;
   }
   return $server->getAccessTokenData(OAuth2\Request::createFromGlobals());
}
