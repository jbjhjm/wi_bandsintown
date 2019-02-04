# Wolf Interactive Bandsintown Module for Joomla 3
Joomla module generating a responsive table out of bandsintown event announcements.
Includes caching mechanism and storage/archive view for past events.
In opposite to javascript-based bandsintown widget it has additional layout/feature settings, adaptive styling and better responsiveness.

# Installation
`git clone https://github.com/jbjhjm/wi_bandsintown.git`

Change to development directory and run `npm install`

# Building
use `grunt build` to create a package for joomla installer at ../release.

use `grunt stage` to generate module within a joomla installation located at ../staging.

You can adjust both paths at the top of gruntfile.js (deployPath, stagePath).
