/* -*- Mode: SQL; indent-tabs-mode: nil; c-basic-indent: 2 -*- 
 * Gallery v2.0 Schema
 * -------------------
 * $Id$
 */

create database gallery;
use gallery;

create table Albums (
  AlbumID int unsigned not null primary key,
  Name varchar not null,
  Description varchar,
  HighlightImageItemID int unsigned not null foreign key (ImageItems.ImageItemID),
  ParentAlbumID int unsigned # if null, then is a root album.
);

create table Items (
  ItemID int unsigned not null primary key,
  AlbumID int unsigned not null foreign key (Albums.AlbumID),
  OwnerID int unsigned,
  ImageItemID int unsigned, # the source image, if there is one
  Path varchar not null # relative path & filename from ALBUM_DIR
  IsHidden char(1) not null, # 'y', 'n' - or whatever our boolean standard will be
  ViewCount int unsigned, # impression counter
  ViewCountStart date, # when the impression counter was last reset to 0 - may be null if never reset.
  Created date not null, # what does this refer to? when this row was inserted? the create date of the item?
  Modified date
  
  # TODO - permissions - how? comma-sep'ed vchar, chmod-style bits, or some other table?
);

# By moving "path" into items, and storing exif data in a flatfile
# cache (it isn't searchable), this table is not needed:

#create table ImageItems (
#  ImageItemID int unsigned not null primary key,
#  Path varchar not null,
#  /* I was going to have these columns, but they really are derivable from Items.path. */
#  ThumbImagePath int unsigned,
#  HighlightImagePath int unsigned,
#  ResizedImageID int unsigned
# /* Heck, even the exif data should be in a flatfile cache, too. */
#  ExifData varchar

create table Owners (
  OwnerID int unsigned not null primary key,
  Username varchar not null,
  Password varchar not null,
  /* what else? full name? The Owner class isn't spec'ed in the g20_class_api. */
);
  
create table Comments (
  CommentID int unsigned not null primary key,
  ItemID int unsigned not null foreign key (Items.ItemID),
  IPaddr varchar not null,
  Email varchar,
  Message varchar,
  Rating tinyint unsigned, # heh. squeek it in?
  Created date not null
)

# Remaining TODO - do we store image-regenerable info in the db? dimensions? exif data?

