# Wolf Interactive Bandsintown Module for Joomla 3
Joomla module generating a responsive table out of bandsintown event announcements.
Includes caching mechanism and storage/archive view for past events.
In opposite to javascript-based bandsintown widget it has additional layout/feature settings, adaptive styling and better responsiveness.

# Building
`git clone https://github.com/jbjhjm/wi_bandsintown.git`

Change to development directory and run `npm install`.

use `grunt build` to create a package for joomla installer at ../release.

# Installation
Check v1.1.0 release zip. You need to install database table manually. Check out /db directory, execute the sql data via phpmyadmin or similar. Didn't create a database installer by now.
