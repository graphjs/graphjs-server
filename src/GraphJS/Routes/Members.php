<?php

return \GraphJS\Utils::convertLegacyRoutes([
    ['GET', '/getMembers','getMembers'], // +
    ['GET', '/getFollowers','getFollowers'], // +
    ['GET', '/getFollowing','getFollowing'], // +
    ['GET', '/unfollow','unfollow'],
    ['GET', '/follow','follow']
]);