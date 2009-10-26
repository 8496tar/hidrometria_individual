
GoPHP - Libraries for Codeigniter and ExtJS that allows rapid administrative interface development

-- Description --

Using GoPHP you can create administrative interfaces for your web applications in a very short time.
You only need to define a business model that are derived from Persistent base model. 
All database operations (table creation, data manipulations) are handled automaticaly.
Using renderer class you can generate a complete interface for data manipulation.

-- Prerequisities --

PHP + MySQL enviroment (almost any standard web hosting package will do).
Codeigniter PHP framework.
ExtJS 2.x library or newer.

-- Installation --

To install Codeigniter visit http://www.codeigniter.com

1. Copy files from models to system/application/models directory.
2. Copy files from library to system/application/library directory.
3. Copy files images directory to root directory (or if already exists copy "ppo" directory inside images into existing images directory).
4. (optional) Download ExtJS library and upload it somewhere on server.
5. Don't forger to include script links into your views where you will be using GoPHP library, so that Ext class can be initialized!

(!) If you host your application on 000webhost.com or other host that adds stats script you must disable it,
or you will get javascript errors on ajax requests!

-- SVN --

You can fetch current source code through :

svn co https://gophp.svn.sourceforge.net/svnroot/gophp gophp

-- Notes --

GoPHP is hosted on SourceForge (http://gophp.sourceforge.net)

Bugs to : http://sourceforge.net/tracker/?group_id=274171&atid=1165144
Mailing lists at : http://sourceforge.net/mail/?group_id=274171


Hope you like the effort! 

Vedran Jukic
