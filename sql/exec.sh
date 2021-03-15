#!/bin/bash

cd $(dirname ${BASH_SOURCE[0]})
/opt/lampp/bin/mariadb -u root -t < $1.sql
