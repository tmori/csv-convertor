#!/usr/bin/bash

NOW=$(TZ='Asia/Tokyo' date +%Y%m%d%H%M%S)
git tag $NOW
git push origin --tags

