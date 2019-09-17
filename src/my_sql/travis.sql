CREATE USER 'web_user'@'localhost' IDENTIFIED BY 'web_user';
GRANT SELECT,INSERT,UPDATE,DELETE,CREATE,DROP ON *.* TO 'web_user'@'localhost';
SET PASSWORD FOR 'web_user'@'localhost' = PASSWORD('sleepovers_password');

CREATE DATABASE IF NOT EXISTS `sleepovers` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `sleepovers`;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `orderitem`;
DROP TABLE IF EXISTS `cartitem`;
DROP TABLE IF EXISTS `venueaccount`;
DROP TABLE IF EXISTS `adminaccount`;
DROP TABLE IF EXISTS `artistaccount`;
DROP TABLE IF EXISTS `staffaccount`;
DROP TABLE IF EXISTS `useraccount`;
DROP TABLE IF EXISTS `address`;
DROP TABLE IF EXISTS `audio`;
DROP TABLE IF EXISTS `blogpost`;
DROP TABLE IF EXISTS `image`;
DROP TABLE IF EXISTS `artistonlyproduct`;
DROP TABLE IF EXISTS `product`;
DROP TABLE IF EXISTS `eventpost`;
DROP TABLE IF EXISTS `mediagroup`;
DROP TABLE IF EXISTS `password`;
DROP TABLE IF EXISTS `artistsong`;
DROP TABLE IF EXISTS `folderpass`;
DROP TABLE IF EXISTS `passwordresetkey`;

CREATE TABLE IF NOT EXISTS `passwordresetkey` (
  `userEmail` varchar(254) NOT NULL,
  hash varchar(98) NOT NULL,
  timestamp datetime DEFAULT NULL,
  PRIMARY KEY (`userEmail`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `mediagroup` (
  `mediaGroupID` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`mediaGroupID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `audio` (
  `audioID` int(11) NOT NULL AUTO_INCREMENT,
  `mediaGroupID` int(11) DEFAULT NULL,
  `filename` varchar(255) NOT NULL,
  PRIMARY KEY (`audioID`),
  FOREIGN KEY (`mediaGroupID`) REFERENCES mediagroup(`mediaGroupID`)
  	ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `blogpost` (
  `blogID` int(11) NOT NULL AUTO_INCREMENT,
  `mediaGroupID` int(11) DEFAULT NULL,
  `title` varchar(400) NOT NULL,
  `timestamp` datetime DEFAULT NULL,
  `isPublic` BOOLEAN DEFAULT 0,
  `text` varchar(4000) DEFAULT NULL,
  PRIMARY KEY (`blogID`),
  FOREIGN KEY (`mediaGroupID`) REFERENCES mediagroup(`mediaGroupID`)
  	ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `image` (
  `imageID` int(11) NOT NULL AUTO_INCREMENT,
  `mediaGroupID` int(11) DEFAULT NULL,
  `filename` varchar(255) NOT NULL,
  PRIMARY KEY (`imageID`),
  FOREIGN KEY (`mediaGroupID`) REFERENCES mediagroup(`mediaGroupID`)
  	ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `product` (
  `productID` int(11) NOT NULL AUTO_INCREMENT,
  `mediaGroupID` int(11) DEFAULT NULL,
  `name` varchar(400) NOT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `price` decimal(7,2) NOT NULL,
  `maxQuantity` int(11) DEFAULT NULL,
  `requiresMediaGroup` BOOLEAN DEFAULT 0,
  `isPublic` BOOLEAN DEFAULT 0,
  PRIMARY KEY (`productID`),
  FOREIGN KEY (`mediaGroupID`) REFERENCES mediagroup(`mediaGroupID`)
  	ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE IF NOT EXISTS `artistonlyproduct` (
  `productID` int(11) NOT NULL,
  PRIMARY KEY (`productID`),
  FOREIGN KEY (`productID`) REFERENCES product(`productID`)
  	ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `address` (
  `AddressID` int(11) NOT NULL AUTO_INCREMENT,
  `street` varchar(255) NOT NULL,
  `lineTwo` varchar(150) DEFAULT NULL,
  `city` varchar(255) NOT NULL,
  `province` SET('BC','AB','MB','NB','NL','NT','NS','NU','ON','PE','QC','SK','YT') NOT NULL,
  `postalCode` varchar(15) NOT NULL,
  PRIMARY KEY (`AddressID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `useraccount` (
  `userEmail` varchar(254) NOT NULL,
  `profileImageID` int(11) DEFAULT NULL,
  `firstName` varchar(200) NOT NULL,
  `lastName` varchar(200) NOT NULL,
  `shippingAddressID` int(11) DEFAULT NULL,
  `billingAddressID` int(11) DEFAULT NULL,
  `isActive` BOOLEAN DEFAULT 1,
  `blogOptIn` BOOLEAN DEFAULT 0,
  `isOnMailList` BOOLEAN DEFAULT 0,
  `banned` BOOLEAN DEFAULT 0,
  PRIMARY KEY (`userEmail`),
  FOREIGN KEY (`profileImageID`) REFERENCES image(`imageID`)
  	ON DELETE SET NULL ON UPDATE CASCADE,
  FOREIGN KEY (`shippingAddressID`) REFERENCES address(`addressID`)
  	ON DELETE SET NULL ON UPDATE CASCADE,
  FOREIGN KEY (`billingAddressID`) REFERENCES address(`addressID`)
  	ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `eventpost` (
  `eventID` int(11) NOT NULL AUTO_INCREMENT,
  `userEmail` varchar(254) NOT NULL,
  `mediaGroupID` int(11) DEFAULT NULL,
  `title` varchar(400) NOT NULL,
  `timestamp` datetime DEFAULT NULL,
  `text` varchar(4000) DEFAULT NULL,
  `isPublic` BOOLEAN DEFAULT 0,
  PRIMARY KEY (`eventID`),
  FOREIGN KEY (`mediaGroupID`) REFERENCES mediagroup(`mediaGroupID`)
  	ON DELETE SET NULL ON UPDATE CASCADE,
  FOREIGN KEY (`userEmail`) REFERENCES useraccount(`userEmail`)
  	ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `adminaccount` (
  `userEmail` varchar(254) NOT NULL,
  PRIMARY KEY (`userEmail`),
  FOREIGN KEY (`userEmail`) REFERENCES useraccount(`userEmail`)
  	ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `artistaccount` (
  `artistName` varchar(200) NOT NULL,
  `userEmail` varchar(254) NOT NULL,
  `artistImageID` int(11) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  PRIMARY KEY (`artistName`),
  FOREIGN KEY (`userEmail`) REFERENCES useraccount(`userEmail`)
  	ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`artistImageID`) REFERENCES Image(`imageID`)
  	ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `cartitem` (
  `productID` int(11) NOT NULL,
  `userEmail` varchar(254) NOT NULL,
  `mediaGroupID` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `shipProduct` BOOLEAN DEFAULT 1,
  PRIMARY KEY (`productID`,`userEmail`, `mediaGroupID`),
  FOREIGN KEY (`productID`) REFERENCES product(`productID`)
  	ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`userEmail`) REFERENCES useraccount(`userEmail`)
  	ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`mediaGroupID`) REFERENCES mediagroup(`mediaGroupID`)
  	ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `orders` (
  `orderID` int(11) NOT NULL AUTO_INCREMENT,
  `userEmail` varchar(254) NOT NULL,
  `shippingAddress` int(11) NOT NULL,
  `billingAddress` int(11) NOT NULL,
  `orderStatus` SET('uncon','con','comp','ship') DEFAULT 'uncon',
  `orderDate` datetime DEFAULT NULL,
  `completionDate` datetime DEFAULT NULL,
  `shipDate` datetime DEFAULT NULL,
  PRIMARY KEY (`orderID`),
  FOREIGN KEY (`userEmail`) REFERENCES useraccount(`userEmail`)
  	ON DELETE NO ACTION ON UPDATE CASCADE,
  FOREIGN KEY (`shippingAddress`) REFERENCES address(`addressID`)
  	ON DELETE NO ACTION ON UPDATE CASCADE,
  FOREIGN KEY (`billingAddress`) REFERENCES address(`addressID`)
  	ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `orderitem` (
  `productID` int(11) NOT NULL,
  `orderID` int(11) NOT NULL,
  `quantity` varchar(255) NOT NULL,
  `mediaGroupID` int(11) NOT NULL,
  `shipProduct` BOOLEAN DEFAULT 1,
  `orderStatus` SET('uncomp','comp') DEFAULT 'uncomp',
  PRIMARY KEY (`productID`,`orderID`,`mediaGroupID`),
  FOREIGN KEY (`productID`) REFERENCES product(`productID`)
  	ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`orderID`) REFERENCES orders(`orderID`)
  	ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`mediaGroupID`) REFERENCES mediagroup(`mediaGroupID`)
  	ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `password` (
  `userEmail` varchar(254) NOT NULL,
  `hash` varchar(98) NOT NULL,
  PRIMARY KEY (`userEmail`),
  FOREIGN KEY (`userEmail`) REFERENCES useraccount(`userEmail`)
  	ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `staffaccount` (
  `userEmail` varchar(254) NOT NULL,
  PRIMARY KEY (`userEmail`),
  FOREIGN KEY (`userEmail`) REFERENCES useraccount(`userEmail`)
  	ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `venueaccount` (
  `venueName` varchar(200) NOT NULL,
  `userEmail` varchar(254) NOT NULL,
  `venueImageID` int(11) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `addressID` int(11) DEFAULT NULL,
  PRIMARY KEY (`venueName`),
  FOREIGN KEY (`userEmail`) REFERENCES useraccount(`userEmail`)
  	ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`venueImageID`) REFERENCES image(`imageID`)
  	ON DELETE SET NULL ON UPDATE CASCADE,
  FOREIGN KEY (`addressID`) REFERENCES address(`AddressID`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `artistsong` (
  `artistName` varchar(200) NOT NULL,
  `mediaGroupID` int(11) NOT NULL,
  `songNumber` int(11) NOT NULL,
  `title` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`artistName`,`mediaGroupID`,`songNumber`),
  FOREIGN KEY (`artistName`) REFERENCES artistaccount(`artistName`)
  	ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`mediaGroupID`) REFERENCES mediagroup(`mediaGroupID`)
  	ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `folderpass` (
  `name` varchar(256) NOT NULL,
  `pass` varchar(98) NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO useraccount VALUES ("user1@email.com", 1, "Davie", "Jones", 1, 1, 1, 0, 0, 0);
INSERT INTO useraccount VALUES ("user2@email.com", 2, "Dock", "Happy", 2, 2, 1, 0, 0, 0);
INSERT INTO useraccount VALUES ("user3@email.com", 3, "Belerie", "Dee", 3, 3, 1, 1, 1, 0);
INSERT INTO useraccount VALUES ("user4@email.com", 4, "Guppy", "Haddock", 4, 4, 1, 1, 1, 0);
INSERT INTO useraccount VALUES ("user5@email.com", 5, "Clairly", "Tyred", 5, 5, 1, 0, 0, 0);
INSERT INTO useraccount VALUES ("user6@email.com", 6, "Nyyd", "Sleap", 6, 6, 1, 1, 1, 0);
INSERT INTO useraccount VALUES ("user7@email.com", NULL, "Shaggy", "Eies", 7, 7, 1, 1, 1, 0);
INSERT INTO useraccount VALUES ("user8@email.com", NULL, "Heffy", "Hed", 8, 8, 1, 1, 1, 0);
INSERT INTO useraccount VALUES ("thedarkneverbotheredmeanyway@secretlayer.com", NULL, "Bat", "Man", 9, 9, 1, 1, 1, 0);

INSERT INTO address VALUES (1, "001 Street Name", "APT#", "City 001", "BC", "A1A1A1");
INSERT INTO address VALUES (2, "002 Street Name", "APT#", "City 001", "BC", "A1A1A1");
INSERT INTO address VALUES (3, "003 Street Name", "APT#", "City 002", "BC", "B2B2B2");
INSERT INTO address VALUES (4, "004 Street Name", "APT#", "City 002", "BC", "B2B2B2");
INSERT INTO address VALUES (5, "005 Street Name", "APT#", "City 003", "AB", "C3C3C3");
INSERT INTO address VALUES (6, "006 Street Name", "APT#", "City 003", "AB", "C3C3C3");
INSERT INTO address VALUES (7, "007 Street Name", "APT#", "City 004", "AB", "D4D4D4");
INSERT INTO address VALUES (8, "008 Street Name", "APT#", "City 004", "AB", "D4D4D4");
INSERT INTO address VALUES (9, "456 1st Street", NULL, "Gotham", "ON", "A5B7T5");

INSERT INTO audio VALUES (1, 1, "song1");
INSERT INTO audio VALUES (2, NULL, "song2");
INSERT INTO audio VALUES (3, 5, "song3");
INSERT INTO audio VALUES (4, NULL, "song4");
INSERT INTO audio VALUES (NULL, 6, "song5");
INSERT INTO audio VALUES (NULL, 2, "song6");
INSERT INTO audio VALUES (NULL, 3, "song7");
INSERT INTO audio VALUES (8, 1, "song8");

INSERT INTO image VALUES (1, 3, "image1");
INSERT INTO image VALUES (2, 4, "image2");
INSERT INTO image VALUES (3, 5, "image3");
INSERT INTO image VALUES (4, NULL, "image4");
INSERT INTO image VALUES (5, NULL, "image5");
INSERT INTO image VALUES (6, NULL, "image6");
INSERT INTO image VALUES (NULL, 1, "image7");
INSERT INTO image VALUES (NULL, 1, "image8");
INSERT INTO image VALUES (NULL, 2, "image9");

INSERT INTO staffaccount VALUES ("user1@email.com"), ("user2@email.com");

INSERT INTO venueaccount VALUES ("Venue 1", "user3@email.com", 3, "We are Venue 1. We serve good laughs and better booze. We get the best artists in town to play here every night of the week, so come on down and stay a while!", 3), ("Venue 2", "user4@email.com", NULL, "Hey! We are Venue 2. Come and see Artist 1 play here on August 9th!", 4);

INSERT INTO artistaccount VALUES ("Artist 1", "user5@email.com", 5, "I am Artist 1. Music is my jam, so I make beats for a living. You can call me at (+++)-+++-++++ . "), ("Artist 2", "user6@email.com", NULL, "We are Artist 2! We are a local band looking to play some gigs at your restaurant, party, or funeral! Give our songs a listen and contact us if you are interested in a booking!");

INSERT INTO adminaccount VALUES ("user1@email.com");

INSERT INTO password VALUES ("user1@email.com", "$argon2i$v=19$m=1024,t=2,p=2$WWtaZjVrVmJ3VlB5MUQzRw$ESEluDuSZOLoGedNaDBMi28R995QfN6BJplgS5g7x/o");
INSERT INTO password VALUES ("user2@email.com", "$argon2i$v=19$m=1024,t=2,p=2$QzVHNklrNzEvRklTdUowRg$g++eYF34ZIFZar/sQ3/qgJjPQ19Xu2VQw3mW8hrrmeU");
INSERT INTO password VALUES ("user3@email.com", "$argon2i$v=19$m=1024,t=2,p=2$VnV0NTFxdFBocnNNMHByNQ$Dp5Bv8pUTJ/fxLuIMJLtmXpliS8XEZalzQyV/i9c7uU");
INSERT INTO password VALUES ("user4@email.com", "$argon2i$v=19$m=1024,t=2,p=2$WGIxR0s4dXhnZ1IuVk5RYw$Tjad/6J8weO+O3VeUN5qd37ZKZmXVEkYpI19Qmld8Qo");
INSERT INTO password VALUES ("user5@email.com", "$argon2i$v=19$m=1024,t=2,p=2$QUlTVUlzSllMVnJVeHczNQ$z4LOpN/3oAMnzcstwYrjCciNnrI9AwRRv5SulB56KgY");
INSERT INTO password VALUES ("user6@email.com", "$argon2i$v=19$m=1024,t=2,p=2$TnpTVmJqSnFtTHg1UDJyRQ$8VamZTLE4orOZz47A8JzsBi31qXjY/3lr9Qfqx7LNV0");
INSERT INTO password VALUES ("user7@email.com", "$argon2i$v=19$m=1024,t=2,p=2$aDJmUjd5TGpJYnc3amlldA$WHAm8dbFQrXktw1HCH2f2JxPDs8OZOi8VEMCrv5h9f4");
INSERT INTO password VALUES ("user8@email.com", "$argon2i$v=19$m=1024,t=2,p=2$RDhUcTB0UEh2Z1dVZXBIag$Hp9EJTigSTCz7qSYHNZcsRNyOi7JuUVoz4mB80h9dNE");

INSERT INTO product VALUES (1, NULL, "Sleepovers T-Shirt", "The perfect t-shirt for our fans and followers. One size fits all!", 22.49, NULL, 0, 1);
INSERT INTO product VALUES (2, NULL, "Sleepovers Ball Cap", "A ball cap with our logo front and center. Nothing says 'I love music' more than a ball cap!", 29.99, NULL, 0, 1);
INSERT INTO product VALUES (3, NULL, "A Broken Record", "Just like your in-laws on christmas, this record never stays quiet!", 79.99, NULL, 0, 1);
INSERT INTO product VALUES (4, NULL, "Custom Vinyl Package", "With this deal, you'll receive a completely custom vinyl with the songs and album cover of your choice, as well as signatures from all our leading artists partnered with the site! You don't want to miss out on this unique opportunity!", 149.99, 50, 1, 1);
INSERT INTO product VALUES (5, NULL, "A Signed Napkin", "Oh... you were expecting someone famous? Well this one was signed by my Uncle Joe.", 199.99, NULL, 0, 1);
INSERT INTO product VALUES (6, NULL, "Summer Special! 2 for 1 Bandanas!", "Camping? Skiing? Living as mistress in the 1800's? Well good for you! Here's a couple banadanas.", 10.00, NULL, 0, 1);
INSERT INTO product VALUES (7, NULL, "Sleepovers Bottle Opener", "Do you have writer's block on your latest album? Take the edge off with our signature bottle opener!", 14.99, NULL, 0, 1);
INSERT INTO product VALUES (8, NULL, "Waveform Engraving", "If you have a favorite song or sound, send it to us, and we'll engrave it into placard! Attach one audio file of your choice with your order. The length of the audio file cannot exceed 5 minutes.", 89.99, NULL, 0, 1);
INSERT INTO product VALUES (9, NULL, "A New Website!", "Contact Trevor, Josh, and Everton for a beautifully designed website! This message was sponsored by Munchies, sleep deprivation, and a boat load of caffeine", 0.00, NULL, 0, 0);
INSERT INTO product VALUES (10, NULL, "Half a Vinyl", "We make a custom vynil with your favorite song on repeat. Half gets mailed to you, half gets mailed to a child in need! Help out today's youth with this unique offer.", 9.99, 50, 1, 1);

INSERT INTO artistonlyproduct VALUES (8);
INSERT INTO artistonlyproduct VALUES (10);

INSERT INTO cartitem VALUES (1, "user1@email.com", 1, 2, 1);
INSERT INTO cartitem VALUES (4, "user1@email.com", 3, 50, 1);
INSERT INTO cartitem VALUES (5, "user4@email.com", 1, 2, 1);
INSERT INTO cartitem VALUES (9, "user3@email.com", 6, 1, 1);
INSERT INTO cartitem VALUES (4, "user3@email.com", 3, 25, 1);
INSERT INTO cartitem VALUES (10, "user8@email.com", 1, 4, 1);

INSERT INTO orders VALUES (1, "user1@email.com", 1, 1, 'uncon', "2019-07-17 11:58:25", NULL, NULL);
INSERT INTO orders VALUES (2, "user2@email.com", 2, 2, 'con', "2019-07-17 12:52:20", NULL, NULL);
INSERT INTO orders VALUES (3, "user4@email.com", 4, 4, 'comp', "2019-07-17 13:12:56", "2019-07-18 13:12:56", NULL);
INSERT INTO orders VALUES (4, "user4@email.com", 4, 4, 'ship', "2019-07-17 13:13:20", "2019-07-19 09:10:56", "2019-07-20 09:10:00");

INSERT INTO orderitem VALUES (1, 1, 2, 1, 1, 'uncomp');
INSERT INTO orderitem VALUES (4, 1, 50, 3, 1, 'uncomp');
INSERT INTO orderitem VALUES (4, 2, 20, 1, 1, 'comp');
INSERT INTO orderitem VALUES (5, 3, 2, 1, 0, 'comp');
INSERT INTO orderitem VALUES (9, 4, 10, 1, 1, 'comp');

INSERT INTO mediagroup VALUES (NULL), (NULL), (NULL), (NULL), (NULL), (NULL), (NULL);

INSERT INTO blogpost VALUES (NULL, NULL, "Blog Post 1", "2019-06-17 17:25:00", 1, "This is what the blog post is about. I hope you like our test blog post. I wrote it from my heart.");
INSERT INTO blogpost VALUES (NULL, NULL, "Blog Post 2", "2019-06-18 17:25:00", 1, "This is what 2nd the blog post is about. I hope you like our test blog post. I wrote it from my heart.");
INSERT INTO blogpost VALUES (NULL, 1, "Blog Post 3", "2019-06-19 17:25:00", 1, "This is what 3rd the blog post is about. I hope you like our test blog post. I wrote it from my heart.");
INSERT INTO blogpost VALUES (NULL, 2, "Blog Post 4", "2019-06-20 17:25:00", 1, "This is what 4th the blog post is about. I hope you like our test blog post. I wrote it from my heart.");
INSERT INTO blogpost VALUES (NULL, 3, "Blog Post 5", "2019-06-21 17:25:00", 1, "This is what 5th the blog post is about. I hope you like our test blog post. I wrote it from my heart.");

INSERT INTO eventpost VALUES (NULL, "user3@email.com", NULL, "Event post 1 by Venue 1", "2019-06-17 17:38:45", "This is an example event post. You can come and hang out at this place at this time, but you will probably be alone since this is fake.",0);
INSERT INTO eventpost VALUES (NULL, "user4@email.com", 2, "Event post 2 by Venue 2", "2019-06-17 17:39:12", "This is the second example event post. You can come and hang out at this place at this time, but you will probably be alone since this is fake.",0);
INSERT INTO eventpost VALUES (NULL, "user4@email.com", 3, "Event post 3 by Venue 2", "2019-06-17 17:40:32", "This is the third example event post. You can come and hang out at this place at this time, but you will probably be alone since this is fake.",0);

INSERT INTO artistsong VALUES ("Artist 1", 7, 1, "New Light");

SET FOREIGN_KEY_CHECKS = 1;