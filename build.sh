#!/bin/bash
#

WHERE=`pwd`

if [ -a .encoded ]; then
  TGZ_NAME="jpcache-enc-1.1.1-1.tgz"
  DIR_NAME="jpcache-enc"
else
  TGZ_NAME="jpcache-1.1.1-1.tgz"
  DIR_NAME="jpcache"
#  ./sdk.sh
fi

rm `find -name "*~"` 2>/dev/null
rm `find|grep "#"` 2>/dev/null
cd ..
tar -cz --exclude=OLD --exclude=work --exclude=*~ --exclude=CVS --exclude=.?* --exclude=np --exclude=.cvsignore -f $TGZ_NAME $DIR_NAME
cd $WHERE
