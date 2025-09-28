#!/bin/bash
service php8.1-fpm start
service cron start
nginx -g "daemon off;"
