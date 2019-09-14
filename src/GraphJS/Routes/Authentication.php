<?php

return \GraphJS\Utils::convertLegacyRoutes([
    ['GET', '/tokenSignup',"tokenSignup"],
    ['GET', '/tokenLogin',"tokenLogin"],
    ['GET', '/signup',"signup"],
    ['GET', '/login',"login"],
    ['GET', '/logout',"logout"],
    ['GET', '/whoami',"whoami"],
    ['GET', '/resetPassword',"resetPassword"],
    ['GET', '/verifyReset',"verifyReset"],
    ['GET', '/verifyEmailCode',"verifyEmailCode"],
]);