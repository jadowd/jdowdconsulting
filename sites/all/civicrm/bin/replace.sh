#!/bin/sh
for i in `find . -name '*.js' -or -name '*.module' -or -name '*.php' -or -name '*.po*' -or -name '*.tpl' -or -name '*.txt'`; do
  echo $i;
  perl -pi -e 's/CiviCRM version .\../CiviCRM version 2.1/' $i;
  perl -pi -e 's/Copyright CiviCRM LLC \(c\) 2004-20../Copyright CiviCRM LLC (c) 2004-2008/' $i;
done
