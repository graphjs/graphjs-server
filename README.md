# GraphJS-Server

An event-driven, non-blocking GraphJS Server. 

Extends [Pho REST Server](https://github.com/phonetworks/pho-server-rest) APIs. For more information, check out http://graphjs.com

GraphJS-Server does not rely on a third party HTTP Server such as [NGINX](https://nginx.org/en/) or [Apache HTTPD](https://httpd.apache.org/). But it is recommended that you run it behind a proxy server for static assets and caching.


## Requirements

* PHP 7.2+
* PHP extensions: bcmath, and sodium
* [Composer](https://getcomposer.org/)
* [Git](https://git-scm.com/)
* Redis: [Install](https://redis.io/topics/quickstart)
* Neo4j: [Install](https://neo4j.com/download/)

## Heroku Installation

Heroku is popular cloud provider by Salesforce. To install, just click the button below, and when asked, fill in the form with your email (you don't need to touch any other fields):

[![Deploy](https://www.herokucdn.com/deploy/button.svg)](https://heroku.com/deploy?template=https://github.com/phonetworks/graphjs-server/tree/master)

> **WARNING for Heroku installations**: Since GrapheneDB (the cloud Neo4J provider) provision takes longer than app's initial build and launch, you will need to reboot (re-deploy) the app once after it's built for the first time. Otherwise, there will be issues with the instance, and you won't be able to run the API calls properly. This is a common issue first-time Heroku installers are facing, please beware.

You may need to modify `app.json` to use for free instance. Remove `formation` and modify `addons` as follows:
```
{
    ...
    "addons": [ "rediscloud", "graphenedb", "cloudcube", "mailgun", "stream" ],
    ...
}
```
For more information, see https://devcenter.heroku.com/articles/app-json-schema

## Manual Installation

In case, heroku installation is not an option for you, here are the steps to install it manually:

1. The recommended way to install pho-server-rest is through git. MacOS and most UNIX operating system come with git equipped.

    ```git clone https://github.com/phonetworks/graphjs-server/```

    > If you are on Windows or don't have git preinstalled, you may download and install git from https://git-scm.com/, 
    > or just download the graphjs-server zip tarball from https://github.com/phonetworks/graphjs-server/archive/master.zip 
    > and extract.

2. Install the PHP dependencies using Composer.

    ```
    composer install
    ```

3. Create a copy **.env.example** file as **.env** file.

4. Update the **.env** file.

    1. Set the Neo4j username and password of **INDEX_URI**.
        For example:
        ```
        bolt://neo4j_username:neo4j_password@localhost:7687
        ```
    2. Set values of **FOUNDER_NICKNAME**, **FOUNDER_EMAIL**, **FOUNDER_PASSWORD**.

    3. Set values of **MAILGUN_KEY**, **MAILGUN_DOMAIN**. (Optional)

## License

MIT, see [LICENSE](https://github.com/phonetworks/pho-microkernel/blob/master/LICENSE).
