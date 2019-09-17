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
  FOREIGN KEY (`artistImageID`) REFERENCES image(`imageID`)
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

SET FOREIGN_KEY_CHECKS = 1;