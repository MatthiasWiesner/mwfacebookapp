# cloudControl example app

This example shows how to develop a simple facebook app hosted on cloudControl.

## Prerequisites

 * [cloudControl user account](https://www.cloudcontrol.com/for-developers)
 * [Git client](http://git-scm.com/)
 * MySQL client, such as [MySQL Workbench](http://dev.mysql.com/downloads/workbench/) or the command-line tools
 * a default facebook and facebook developer account

## Create the app

The example app should show the details for the application and deployments hosted on cloudControl.
After the login to facebook, you can request the cloudControl API. 
First you are asked for your email and password. After the successfull authorization your applications are listed.

### Session to database

It is needed to store the cloudControl API token in the session. 
On cloudControl it is hardly recommended to store the session data in the database.
To do this, you have to add the mysqls add-on to your deployment.

#### database schema
After adding the mysqls addon you have to create the session table
~~~sql
CREATE TABLE `session` (
    `session_id` varchar(255) NOT NULL,
    `session_value` text NOT NULL,
    `session_time` int(11) NOT NULL,
    PRIMARY KEY (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
~~~
The mysqls credentials are written to the cloudControl credentials file and read from the `CloudControlController::getCredentials` method.

#### register classes

Now you have to register the session and the database handler. See more at `public/index.php`.
The accordingly classes to register are:
~~~
Silex\Provider\DoctrineServiceProvider
Silex\Provider\SessionServiceProvider
~~~

### Facebook Login

Before every request you should check if you are logged into facebook. For this you use the `$app->before` functionality.
Here the facebook's api are requested for your login state. For this the facebook developer credentials are used.
The facebook developer credentials should be stored outside of your repository. You can use the cloudControl config addon:
~~~bash
cctrlapp mwfacebookapp/default addon.add config.free --APP_ID=<YOUR_FB_APP_ID> --SECRET_KEY=<YOUR_FB_SECRET_KEY>
~~~
Now, the credentials are stored in the cloudControl credentials file.

Btw. because of the session has already been started, the Facebook object has to instantiate in the closure.