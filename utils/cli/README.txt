This directory contains a number of cli install templates named with the convention:

cli.[major-version].php

They are used by the various utils scripts to find the most suitable command to use
for a particular install. It does this by figuring out the site version, then picking
the install script which has the highest version but is still less than or equal to the
site's version.

So if your cli/ directory contains 1.1, 2.2 and 2.4 files it would use the following:

Site version        Selected script
2.5                 2.4
2.4.3               2.4
2.4.0               2.4
2.3                 2.2
2.2.9               2.2
1.1                 1.1
1.0                 Error - no suitable script

You can customise each script to contain settings only suitable for that version or above.
