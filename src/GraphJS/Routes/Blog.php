<?php

return \GraphJS\Utils::convertLegacyRoutes([
    ["GET", "/getBlogPosts",'getBlogPosts'], // +
    ["GET", "/getBlogPost",'getBlogPost'], // +
    ["GET", "/startBlogPost",'startBlogPost'], 
    ["POST", "/startBlogPost",'startBlogPost'], // +
    ["GET", "/editBlogPost",'editBlogPost'],
    ["POST", "/editBlogPost",'editBlogPost'], // +
    ["GET", "/removeBlogPost",'removeBlogPost'],
    ["GET", "/unpublishBlogPost",'unpublishBlogPost'],
    ["GET", "/publishBlogPost",'publishBlogPost'],
    ["GET", "/unpin",'unpin'],
    ["GET", "/pin",'pin']
]);