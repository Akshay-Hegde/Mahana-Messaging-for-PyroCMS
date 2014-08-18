Mahana for PyroCMS
==================

Step 1: installation
-----
Install this module as you would with any PyroCMS module by putting it in shared_addons/modules/ and installing it in the CMS. This will create the tables that you'll need. 

Step 2: writing your own messaging system
------
When the module is installed and enabled, the library is magically autoloaded (events.php) so you can start using it right away in your own modules by calling this for example:

`$threads = $this->mahana->get_all_threads_grouped($this->current_user->id);`

All available methods can be found in mahana/libraries/mahana.php. Documentation will follow.

Step 3: connect messages to a stream (optional)
------
This is useful if messages are about a stream entry. For each message that is send, you put a stream entry id in the subject field in your controller. For example, if you have a car selling platform, you probably have a stream with 'cars'. You should go to Settings -> Mahana and select the cars stream. When you now check the Mahana messages panel in your admin panel, the subject field (where you saw id of car entries) is replaced with the title field of your stream.

Please note
------
Uninstalling this module will delete its tables and thus all messages!

Credits for Mahana
----------
* Name:  Mahana Messaging Library for CodeIgniter
*
* Author: Jeff Madsen
*		  jrmadsen67@gmail.com
*       http://www.codebyjeff.com
*
* Location: - git@github.com:jrmadsen67/Mahana-Messaging-library-for-CodeIgniter.git
