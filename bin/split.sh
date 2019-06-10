#!/usr/bin/env bash

set -e
set -x

CURRENT_BRANCH="develop"

function split()
{
    SHA1=`./bin/splitsh --prefix=$1`
    git push $2 "$SHA1:refs/heads/$CURRENT_BRANCH" -f
}

function remote()
{
    git remote add $1 $2 || true
}

git pull origin $CURRENT_BRANCH

remote code-coverage-bridge git@github.com:doyolabs/code-coverage-bridge.git

split 'src/Bridge/CodeCoverage' code-coverage-bridge
