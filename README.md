# BIRD Metadata Management API #

Provides endpoints for serving bird metadata as XML

This plugin was based on / inspired by https://github.com/catalyst/moodle-webservice_restful

## Install
Install this plugin under `local`.

## After Installation
- activate webservices
- activate rest protocol
- create a new role or use existing one that allows
  - `webservice/bird:use`
  - `moodle/webservice:createtoken`
  - `webservice/rest:use`
- create a new user or use existing one that uses above role
- Add this user to Authorised users for webservice "BIRD Metadata Management"
- create token for that user and Webservice "BIRD Metadata Management"

## Access
You can use the following URL: `<base-url>/local/bird_mdm/server.php/<endpoint>/`

Valid endpoints are:
  - bird_academy
  - bird_program
  - bird_module
  - bird_course.

You will have to send your token via HTTP Header `Authorization: Token <token>`.

## License ##

2022 Roland Hager <kontakt@roland-hager.de>

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program.  If not, see <https://www.gnu.org/licenses/>.
