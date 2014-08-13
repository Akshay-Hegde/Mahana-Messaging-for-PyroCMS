Mahana for PyroCMS
==================

Step 1
-----
Install this module as you would with any PyroCMS module by putting it in shared_addons/modules/ and installing it in the CMS. This will create the tables that you'll need. 

Step 2.
------
When installed and enabled, the library is magically autoloaded so you can start using it right away in your own module(s) by calling this for example:

`$threads = $this->mahana->get_all_threads_grouped($this->current_user->id);`

All available methods can be found in mahana/libraries/mahana.php. Documentation will follow.

Please note:
------
**Uninstalling this module will delete its tables and thus all messages!**

Original credits:
----------
* Name:  Mahana Messaging Library for CodeIgniter
*
* Author: Jeff Madsen
*		  jrmadsen67@gmail.com
*       http://www.codebyjeff.com
*
* Location: - git@github.com:jrmadsen67/Mahana-Messaging-library-for-CodeIgniter.git
