This directory contains a number of config templates named with the convention:

config.[major-version].php

They are used by the various utils scripts to find the most suitable config file
for a particular install. It does this by figuring out the site version, then picking
the config file which has the highest version but is still less than or equal to the
site's version.

So if your conf/ directory contains 1.1, 2.2 and 2.4 files it would use the following:

Site version        Selected config
2.5                 2.4
2.4.3               2.4
2.4.0               2.4
2.3                 2.2
2.2.9               2.2
1.1                 1.1
1.0                 Error - no suitable config

You can customise each config to contain settings only suitable for that version or above.
