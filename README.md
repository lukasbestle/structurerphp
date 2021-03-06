# Structurer.php [![Build Status](https://travis-ci.org/vis7mac/structurerphp.png?branch=master)](https://travis-ci.org/vis7mac/structurerphp)

> A prototyping library to build folder structure using JSON files. Like zip but without zip.

You have an app needing some kind of file storage with a specific folder structure, maybe with `logs` and `config` directories?
You want that app to create that structure automatically without having to use something like `zip` or `tar`?

There you go!

## Where it comes from and what it does

There is a Mac app by NetTuts+ called `Structurer` doing the same thing, but for projects. That gave me the inspiration to code something like that in PHP.
You can download it from [here](http://net.tutsplus.com/freebies/others/free-mac-utility-app-structurer/).

This app gives you the ability to create a folder structure with blank files and to download stuff from the internet while this library is more to create fixed structures with contents already in it. Like I said above: It's like zip without zip.

## The CLI

`Structurer.php` has a CLI called `structurer` to save existing structures as `.structure` files - the files `Structurer.php` can then use to reproduce the same structure.

The CLI can also reproduce the structures itself if you ever need that.

Basically, it's the whole library with command line magic.

### Installation

There's an installation script in that repo (`cli/install.php`) you can use.

You can also just throw that command into your terminal and be happy *(yeah, that CLI is Unix-only, no Windows here)*:

	$ php -r "$(curl -fsSkL raw.github.com/vis7mac/structurerphp/master/cli/install.php)"

This script combines the library with the command line magic and puts it into `/usr/bin/`.

Please open a new shell session to use it.

### Use without installation

If you don't have sudo permission or don't want to install something, simply call the file `cli/cli.php` using

	php <PATH_TO_REPO>/cli/cli.php

This is the same as calling the installed `structurer`.

### Usage

You should now have `structurer` installed.

Simply call `structurer` in your terminal to test if it works.

#### Structurize

Pack the current directory and save a `<DIRNAME>.structure` file into it:

	structurer structurize

----

Pack a specific directory and save a `<DIRNAME>.structure` file into the current directory:

	structurer structurize <PATH>

----

Save the structure file to a specific file:

	structurer structurize [<PATH>] > file.structure

----

Compress the structure file and save it:

	structurer structurize -c [<PATH>] [> file.structure]

#### Destructurize

Unpack into the current directory (will overwrite existing files):

	structurer destructurize <FILENAME>.structure

----

Unpack into a specific directory (will overwrite existing files):

	structurer destructurize <FILENAME>.structure <PATH>

#### Check

Check if a `.structure` file fits to a directory:

	structurer check <FILENAME>.structure <PATH> [-d] [-a] [-c]

- `-d`: Check file deletions (activated by default)
- `-a`: Check new files
- `-c`: Check file changes

## The library

You can find some example code in `demo/demo.php`.