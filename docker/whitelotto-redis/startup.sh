#!/bin/bash
set -e

exec gosu redis "$@"