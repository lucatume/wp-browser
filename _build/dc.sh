#!/usr/bin/env bash

# Wrapper script to run docker-compose is an OS cross-compatible manner.

# Fix filemodes only on Linux, macOS and Windows will do that at the (Docker) VM level.
FIXUID=0 && [ "${OSTYPE:0:5}" == 'linux' ] && FIXUID=1
export FIXUID=${FIXUID}
DOCKER_RUN_USER='' && [ "${OSTYPE:0:5}" == 'linux' ] && DOCKER_RUN_USER="$(id -u)"
export DOCKER_RUN_USER=${DOCKER_RUN_USER}
DOCKER_RUN_GROUP='' && [ "${OSTYPE:0:5}" == 'linux' ] && DOCKER_RUN_GROUP="$(id -u)"
export DOCKER_RUN_GROUP=${DOCKER_RUN_GROUP}

docker-compose -f docker-compose.yml $@
