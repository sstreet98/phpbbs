CREATE DATABASE bbs;
USE BBS;
CREATE TABLE users (
    id              bigint          AUTO_INCREMENT PRIMARY KEY,
    name            varchar(36)     UNIQUE KEY NOT NULL,
    email           varchar(255)    UNIQUE KEY NOT NULL,
    password        char(40)        NULL,
    security        int             DEFAULT 10,
    firstlogin      datetime        DEFAULT UTC_TIMESTAMP(),
    lastlogin       datetime        DEFAULT UTC_TIMESTAMP(),
    lastpage        varchar(256)    NULL,
    validated       boolean         DEFAULT false,
    valcode         varchar(256)    DEFAULT NULL,
    lastactivity    timestamp       
);

CREATE TABLE hdrcache (
    area            varchar(60)     NOT NULL,
    msgnum          int             NOT NULL,
    hdr             text            NOT NULL,
    lastmod         int             NOT NULL
);
CREATE UNIQUE INDEX hdrcache_key ON hdrcache(area,msgnum);

CREATE TABLE akas (
    id              bigint          AUTO_INCREMENT PRIMARY KEY,
    aka             varchar(20)     NOT NULL
);

CREATE TABLE origins (
    id              bigint          AUTO_INCREMENT PRIMARY KEY,
    origin          varchar(70)     NOT NULL
);

CREATE TABLE msgareas (
    id              bigint          AUTO_INCREMENT PRIMARY KEY,
    orderid         bigint          DEFAULT 0,
    name            varchar(40)     UNIQUE KEY NOT NULL,
    descr           varchar(255)    NULL,
    type            int             DEFAULT 0,
    readsec         int             DEFAULT 0,
    writesec        int             DEFAULT 10,
    sysopsec        int             DEFAULT 32000,
    path            varchar(255)    NOT NULL,
    reqattrib       int             DEFAULT 0,
    optattrib       int             DEFAULT 0,
    akaid           bigint          NULL,
    originid        bigint          NULL
);