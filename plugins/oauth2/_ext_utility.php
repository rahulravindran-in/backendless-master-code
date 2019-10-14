<?php

require_once __DIR__.'/../../../../plugins/<projectHash>/oauth2/server.php';

function enable_oauth2($scopeRequired){
   $request = OAuth2\Request::createFromGlobals();
   $response = new OAuth2\Response();
   if (!$server->verifyResourceRequest($request, $response, $scopeRequired)) {
       $response->send();
       die;
   }
}
