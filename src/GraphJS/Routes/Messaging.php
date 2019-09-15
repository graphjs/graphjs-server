<?php

return \GraphJS\Utils::convertLegacyRoutes([
    ['GET', '/sendAnonymousMessage','sendAnonymousMessage'],// + 
    ['GET', '/sendMessage','sendMessage'], // + 
    ['GET', '/countUnreadMessages','countUnreadMessages'], // + 
    ['GET', '/getInbox','getInbox'], // + 
    ['GET', '/getOutbox','getOutbox'], // + 
    ['GET', '/getConversations','getConversations'], // + 
    ['GET', '/getConversation','getConversation'], // + 
    ['GET', '/getMessage','getMessage']  // +
]);
