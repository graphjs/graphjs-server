<?php

return \GraphJS\Utils::convertLegacyRoutes([
    ['GET', '/sendAnonymousMessage','message'],
    ['GET', '/sendMessage','message'],
    ['GET', '/countUnreadMessages','countUnreadMessages'],
    ['GET', '/getInbox','getInbox'],
    ['GET', '/getOutbox','getOutbox'],
    ['GET', '/getConversations','getConversations'],
    ['GET', '/getConversation','getConversation'],
    ['GET', '/getMessage','getMessage']
]);
