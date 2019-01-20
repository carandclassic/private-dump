# Private Dump
Private Dump is a CLI script which can dump your MySQL database, and sanitise the output for development/staging use.

It accomplishes this by reading a JSON configuration file which maps out which table columns should be replaced, and by what.

Private Dump requires PHP >= 5.6.0



# Table of Contents

- [Installation](#installation)
- [Usage](#usage)
- [Configuration File](#configuration-file)
- [Replacements](#replacements)
  - [Text](#text)
  - [Dates](#dates)
  - [Internet](#internet)
  - [Random](#random)
  - [User](#user)
  - [Payment](#payment)
  - [Company](#company)
  - [Miscellaneous](#miscellaneous)
  - [Barcodes](#barcodes)
- [Transformers](#transformers)
- [Notes](#notes)
- [Dev Steps](#dev-steps)
- [Release Process](#release-process)



# Installation

Install with [Composer](https://getcomposer.org/)

```
composer require ashleyhindle/private-dump
```



Install with [curl](https://curl.haxx.se/)

```
curl -o private-dump https://github.com/ashleyhindle/private-dump/releases/download/0.01/private-dump.phar
chmod a+x private-dump 
```



# Usage

When private-dump is ran with a valid configuration it will output the dump to stdout, allowing you to redirect it to the file you like, or pipe it to another program (compression, transfer, encryption, etc..).



First, create a [configuration file](#configuration-file) manually or from an [example config](configs/), then:



**Composer**:

- `vendor/bin/private-dump.phar -c private-dump.json > /backups/mysql-backup-with-sensitive-data-overwritten.sql`

**Curl**:
- `private-dump -c private-dump.json > /backups/mysql-backup-with-sensitive-data-overwritten.sql`



You can also override the MySQL username, password and hostname from the command line:

**Composer**:
```bash
vendor/bin/private-dump.phar -c private-dump.json -u bigben -p bingbong -h rds-213121231-13gb.amazon.com > /backups/mysql-backup-with-sensitive-data-overwritten.sql
```

**Curl**:
```bash
private-dump -c private-dump.json -u bigben -p bingbong -h rds-213121231-13gb.amazon.com > /backups/mysql-backup-with-sensitive-data-overwritten.sql
```



*Note*: It's best not to pass the password on the command line as it can be seen in the process list, and will exist in the user's history. 



# Configuration File



# Replacements

The vast majority of these are made possible by the amazing [Faker library](https://github.com/fzaninotto/Faker).  Most formatters listed in [Faker's documentation](https://github.com/fzaninotto/Faker#formatters) are supported in Private Dump's configuration file

All replacements below should be prefixed with an `@` as in the [example configuration files](configs).

If you need to use a hardcoded value (active=0, completed=1) you can do this by omitting the `@`: `"active": 0` in the configuration file.  



#### Text

- `string` - Random length string up to 255 characters
- `realText` - Quotes from books
- `loremSentence` - 1 sentence of Lorem
- `loremParagraph` - 3 sentences of Lorem
- `loremParagraphs` - 3 paragraphs of Lorem

#### Dates

- `iso8601` - 2019-01-20
- `iso8601Recent` - ISO 8601 date in the last 3 months

#### Internet
- `email` - bigben@example.com
- `url` - https://www.parliament.uk/bigben
- `ipv4`
- `ipv6`
- `userAgent`
- `domainName` - bigben.net

#### Random

- `randomDigit` - singular digit
- `randomNumber` - up to 8 digits
- `randomLetter` 
- `randomString` - Random length string up to 255 characters

#### User

- `firstName`
- `lastName`
- `title` - Ms. Mr. Dr.
- `fullName` - Brian May
- `fullAddress` - One line: Building number, street, city, state/county, postcode/zip
- `buildingNumber` - 368
- `streetName` - Broadway
- `streetAddress` - 368 Broadway
- `city` - London
- `postcode` - SW1A 0AA
- `country` - England
- `state` - Texas
- `county` - London
- `latitude` - 51.5008
- `longitude` - `-.1246`
- `phoneNumber`
- `email` - bigben@example.com
- `username` - BigBen
- `url` - https://www.parliament.uk/bigben
- `ipv4` - IPv4 Address
- `ipv6` - IPv6 Address

#### Payment

- `creditCardType` - Mastercard
- `creditCardNumber` - 4444 1111 2222 3333
- `creditCardExpirationDate` - 04/22
- `creditCardExpirationDateString` - '04/13'
- `iban` - BI6B3N8497112740YZ575DJ28BP4
- `swiftBicNumber` - BIGBEN22263

#### Company

- `company` - Company-Name
- `jobTitle` - Croupier

#### Miscellaneous

- `boolean`
- `md5`
- `sha1`
- `sha256`
- `countryCode` - UK
- `currencyCode` - EUR

#### Barcodes

- `barcodeEan13`
- `barcodeEan8`
- `barcodeIsbn13`
- `barcodeIsbn10`



# Transformers



- `uppercase`
- `lowercase`



# Notes

- This would not be possible without [Faker](https://github.com/fzaninotto/Faker)
- Inspired by [GDPR-Dump](https://github.com/machbarmacher/gdpr-dump)

----





*These notes are mainly for my own development use, feel free to ignore.*



# Dev Steps

1. Install [Box](https://box-project.github.io/box2/)
2. Modify PHP configuration to set `phar.readonly = Off`
3. `box build`
4. `chmod a+x bin/private-dump.phar`



# Release Process

1. Build the PHAR: `box build`
2. Update the version in `README.md`'s installation instructions
2. Tag the next release: `git tag -a vx.x.x -m "Release x.x.x"`
3. Push: `git push origin --tags`
4. [Create release on GitHub](https://github.com/ashleyhindle/private-dump/releases/new) attaching the newly created `bin/private-dump.phar` file