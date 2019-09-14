<?php

return \GraphJS\Utils::convertLegacyRoutes([
    ["GET", "/isStarred","isStarred"],
    ["GET", "/unstar","unstar"],
    ["GET", "/star","star"],
    ["GET", "/addComment","addComment"],
    ["GET", "/removeComment","removeComment"],
    ["GET", "/editComment","editComment"],
    ["GET", "/getComments","getComments"],
    ["GET", "/getMyStarredContent","getMyStarredContent"],
    ["GET", "/getStarredContent","getStarredContent"],
    ["GET", "/getPrivateContent","getPrivateContent"],
    ["GET", "/addPrivateContent","addPrivateContent"],
    ["GET", "/editPrivateContent","editPrivateContent"],
    ["GET", "/deletePrivateContent","deletePrivateContent"],
    ["GET", "/listPrivateContents","listPrivateContents"],
]);


