# stistab
- stistab.php is a short script that processes data from information system STIS (https://stis.ping-pong.cz/) and returns it as a result table of the selected competition.
- You can take it and implement to your club web. After you set a few things in stistab.ini file just call the php script that returns you a html table.
- The ini file allows you to set the attributes of the `table` element, mark the selected team name with html elements and attributes or whole row and choose between total score table or home and away table and more.

## stistab.ini file options
The following three settings are mandatory and it is essential that their values remain as they are in ini file:
- **teamStartUrl** - beginning of the link to the team (Do not change!)
- **urlStart** - the beginning of link for data call (Do not change!)
- **urlEnd** - end of link for data call (Do not change!)

Option `url` is important. It is for url address from STIS (https://stis.ping-pong.cz/) of the page on which the table you want is located:
- **url** - the address of the table to be processed, example: `url=https://stis.ping-pong.cz/tabulka/svaz-420000/rocnik-2021/soutez-4402`

Other settings are not required:
- **getUrl** - '1' to allow you to specify settings via url GET parameters | '0' default
- **table** - 0=total_match_table (default) | 1=home_match_table  d| 2=away_match_table
- **tableAttributes** - attributes for `<table>` element, example: tableAttributes='id="tbl" class="table table-striped table-sm"'
- **teamName** - team designation for other 'team..' options
- **teamRowAttributes** - attributes for <tr> element on 'teamName' row
- **teamNameElemStart** - adding elements around (left) the team name, example: `<strong>`
- **teamNameElemEnd** - adding elements around (right) the team name, example: `</strong>`
- **addLinks** - '1' for adding links to the team overview 
- **blankLinks** - '1' for opening links in new tab

## Options via GET method
All options can be entered using GET method parameters. The only condition is set option `getUrl=1` in your stistab.ini file. In case of `getUrl=1` the seted options in stistab.ini file are default. Specified options from GET method parameters rewrite these in stistab.ini file.

#### Example
- Example of stistab.ini file allow GET method, set away match table, set `<strong><em>` elements around team SKST Vlašim A and does not allow links.
```
getUrl=1
teamStartUrl='https://stis.ping-pong.cz/druzstvo-'
urlStart='https://stis.ping-pong.cz/api/?q=tabulka/'
urlEnd='/format.json'
table=2
teamName='SKST Vlašim A'
teamNameElemStart='<strong><em>'
teamNameElemEnd='</em></strong>'
addLinks=0
```

- Call sets `url` of the page on which the table is and rewrites options `addLinks` and `table`. Other options are taken from stistab.ini file.
```
/stistab.php?url=https://stis.ping-pong.cz/tabulka/svaz-420000/rocnik-2021/soutez-4411&addLinks=1&table=0
```

- The result is in the picture below.<br>
![stistab](https://user-images.githubusercontent.com/94744070/151730210-d7e47417-aa81-4bd7-a652-c83ac9305afc.png)
  
## Version and changes
*actual version (script, ini file): 1.0*
