# clone category

a plugin to perform a backup/restore of every course in a category to a newly speicifed category, modifying some properties as you go.

## Installation

Put into your /local/ plugins folder and hit up the site notifications as admin

or `cd ./local && git clone https://github.com/frumbert/clonecategory.git clonecategory`

## Usage

First, some assumptions.

1. Your categories use IDNUMBERs. These will be used as a suffix to the cloned course shortnames.
2. Your courses use shortnames that fit the format XXXXX_YYYYY where the segments are separated by an underscore.
3. Cloned courses will have a new shortname consisting of the XXXXX part from the source course, plus underscore, plus the IDNUMBER of the *destination* category.
4. Cloned courses will have their startdate and enddate set.

So, how this works:

1. Be the moodle administrator.
2. Navigate to your admin > local plugins > Clone Category
3. Select the source category, and destination category. You can optionally create the destination category by entering both the NAME and IDNUMBER (both must be entered to create a category, which will be created underneath the Desination Parent Category)
4. Set the course start and end dates. The default dates are today and three months from today. You'll probably want to change this.
5. Press the Clone Courses button, and then go make yourself a hot beverage or take a long stroll. This process can take many minutes to hours, depending on your source category size

## Notes

If debugging is turned on, you'll see some output logs once the process completes showing you memory usage and cpu cycles per backup etc.

## Licence

GPL3, same as Moodle
