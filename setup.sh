#!/usr/bin/env bash

printf "\n--- Installing NodeJS dependencies...\n"
npm install -g gulp
npm install -g bower

printf "\n--- Running npm install...\n"
npm install

printf "\n--- Running bower install...\n"
bower install

printf "\n--- Running composer install...\n"
composer install

printf "\n--- Creating .env..."
cp .env.example .env

printf "\n--- Doing initial build..."
gulp

printf "\n\nComplete, see README for commands/info!\n"
