# Prestron - SMS Home Access Management

## PHP7/MySQL/Twilio +  IoT widgets 

As various Home IoT access hardware and apps come and go, I've still never been able to find a system that works as well as SMS.

There is no onboarding for SMS and as such, for the application of allowing long and short-term access to your home, it is ideal. My plumber can use it, all the handypersons, and all my friends, family and visitors! This system gives you complete control and visibility into home access, and is relatively secure. When I moved house, and for a time, didn't have the system, my friends kept asking for it. Okay... so if I had friends I'm sure they would have.

You will need:

* A Twilio account, and number. www.twilio.com
* A host server with PHP, nginx, mysql (Heroku Hobby at $7 a month is ideal)
* Some way of unlocking or locking your house via API. My old place had a gate buzzer (like a lot of places in San Francisco - so a Smartthings compatible relay in parallel with the buzzer worked great) and my new place has an August Smart Lock which I can control... via IFTTT... via Smartthings. Did someone say Rube Goldberg?
* An IFTTT account, if yer using web'ooks! (Webhooks)

## Instructions

1. Deploy the app.
2. Build the MySQL table.
3. Get a Twilio number and point SMS messages to your app URL
4. Make an IFTTT webhook that unlocks your door with the event 'front_door_unlock' and locks it with 'front_door_lock'
5. Set your environment variables

```
HOUSENAME - Your home's name - I recommend something that ends in -tron. Defaults to Domotron if not set.
JASWDB_URL - Database URL
TWILIO_ACCESS_TOKEN - Get this from your Twilio Account
TWILIO_ID - ditto
TWILIO_NUMBER - double ditto (format is +1XXXYYYZZZZ)
ADMIN_0 - your phone number (format is +1XXXYYYZZZZ)
IFTTT_MAKER_KEY - get this from your IFTTT account
DISABLE_WEB - set to true if you're worried someone fishy might try to guess your app's url for nefarious purposes
MYNAME - your name. Defaults to Piotr Skut if not set.
NONADMIN_NUMBER - set this to a non-admin number for web-interface testing
SMARTTHINGS_KEY - you get the idea
AUGUST_DEVICE_ID - "
```
6. Browse to your app and start playing with it. The web interface is a real version of the app, but will fake 

## Security

Only the admin user can grant and revoke access, and in so doing can set the number of days for which access is allowed. The user is provided a unique 5-alphanumeric code, which must be included in any subsequent messages to the house, in order to control it.

In addition, the sender's number, and the time window are validated, and the admin is notified of *any* messages to the house, whether successful or not, including the sender's number.

So yes, it's SMS, but there are quite a few security measures here:

* Don't publish your Twilio number!
* You will be notified of any message sent to your Twilio number.
* Actual control is done via IFTTT webhook and Smartthings..
* All the above checks. (Unique code, sender's number, time window)

## Admin Syntax

To allow a new user access the Admin sends the following to the house (Twilio) number. Remember this only works from the admin's phone.

`allow <one-word-name> <10-digit-phone-num> <number-days-access>`

To remove access for a user, just send again with 0 for the number of days access.

Example:

`allow Vanessa 5556667777 5` - this will allow Vanessa 5 days' access starting immediately, it will message Vanessa with instructions, and confirm with a message back to the admin

Note the name must be one word only - letters only. No Barry Manilow, no Boutros-Boutros Ghali. Sorry, Kei$ha can't come visit. (An easy fix if anyone wants to contribute but I've never needed it)

Any message sent to the house, by the admin, that does *not* contain the keyword 'allow', will simply open the door.

## User Syntax

`<5-alpa-code>` - unlocks door
`<5-alpha-code> lock` - locks door

Tres simple!

## Web Interface

You can run the app via a web interface too, just point your browser to your host url. I've made all the web UI code render conditional on a browser being detected, so that it doesn't get processed if Twilio calls it. I'm not sure if it matters but at some level it's pleasing to me. I think maybe it reduces my carbon footprint by 0.000000001 grams.
 

## Configure MYSQL

Here's the SQL to build the visitor table.

```
CREATE TABLE `visitors` (
  `P_ID` int(11) NOT NULL AUTO_INCREMENT,
  `FirstName` varchar(15) DEFAULT NULL,
  `PhoneNum` varchar(16) DEFAULT NULL,
  `AccessCode` varchar(16) DEFAULT NULL,
  `EndAccess` datetime DEFAULT NULL,
  `HasBeen` varchar(4) DEFAULT NULL,
  `StartAccess` datetime DEFAULT NULL,
  `LastUse` datetime DEFAULT NULL,
  PRIMARY KEY (`P_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
```

## Add ons

I also turn on my porch lights for any visitors, if it's after sunset (and before sunrise). Alas I was making too many API calls to the service (also use it for motion detection) so ended up hard-coding the times for my lat-long. Turns out they don't change that much each year so just indexed every day of 2020 (leap year) and will probably just keep cycling through that until the apocalypse.

Let me know if you have any suggestions, ideas, pull requests, and drop me a line if you need any help with the install. I'm jamesflynn on twitter... instagram, gmail, facebook etc etc.

Once you have fully-controllable, (relatively) secure, SMS housekeys you'll never make anyone download an app again!

Oh and there are three other routines in the root directory, you can ignore.

* goodnight.php is a script I call to shut the house down (turn lights off, lock doors, activate motion-detecing photon cannons).
* motion.php - trying to get my *stupid* nest cameras to turn on the lights! (so much harder than it sounds... because they *suck*)
* sentry.php [WIP] - this is a script I call on a cron job every few minutes that will run through some checks, scan for bogies, and lights left on.

## Picture
![Text interactions](https://pbs.twimg.com/media/CBjb8zxVEAAvqIP.png)



