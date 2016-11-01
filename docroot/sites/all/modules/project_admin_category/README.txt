$Id: README.txt,v 1.1 2008/05/27 04:05:45 jpetso Exp $

Project admin category - Provides a new "Project administration" category
in the admin interface, for other modules to depend on.


SHORT DESCRIPTION
-----------------
In just two tiny functions, this module does nothing but provide a category
named "Project administration" in the admin overview. Other modules can depend
on this one instead of creating their 'admin/project' menu paths all by
themselves.

This module makes sense because there is no single project management related
module that all project management related sites use, and without this module,
other ones must either duplicate the 'admin/project' menu path with special
care, or put themselves into 'admin/settings' in order to avoid complications.
By depending on this module however, other modules can simply register
'admin/project/*' menu paths and be done with it.

Target audience: modules like Project, Version Control API,
                 Time budget or Project forecast.

No module dependencies.


AUTHOR & MAINTAINER
-------------------
Jakob Petsovits <jpetso at gmx DOT at>
