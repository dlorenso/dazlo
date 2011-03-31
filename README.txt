Welcome to the Dazlo Framework! 

SYSTEM REQUIREMENTS
-------------------

Dazlo Framework requires PHP 5.3.0 or later.

INSTALLATION
------------

Dazlo Framework requires no special installation steps. Simply download the 
framework, extract it to the folder you would like to keep it in, and add the
library directory to your PHP include_path.

Sample code to use the Daz_Loader to autoload all Dazlo Framework classes:

  define('DAZLO_LIB_DIR', ... <path where you installed Dazlo Framework library> ...);

  // register Dazlo Framework autoloader
  include DAZLO_LIB_DIR . '/Daz/Loader.php';
  Daz_Loader :: register(DAZLO_LIB_DIR, ...<additional PHP include paths>...);

QUESTIONS AND FEEDBACK
----------------------

No documentation has been created yet, and the website is not yet complete.
If you find code in this release behaving in an unexpected manner or
contrary to its documented behavior, please contact the author:
  
  D. Dante Lorenso
  dante@lorenso.com
  
LICENSE
-------

The files in this archive are released under the new BSD license.
You can find a copy of this license in LICENSE.txt.

ACKNOWLEDGEMENTS
----------------

The Dazlo Framework team would like to thank all the contributors to the Dazlo
Framework project, our corporate sponsor, and you, the Dazlo Framework user.
Please visit us sometime soon at http://dazlo.org.
