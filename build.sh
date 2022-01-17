#!/bin/bash

rm -v bird_mdm.zip
zip -r bird_mdm.zip bird_mdm -x bird_mdm/.git/\* bird_mdm/stub.php bird_mdm/build.sh