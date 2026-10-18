DROP DATABASE IF EXISTS nrb_db;
CREATE DATABASE nrb_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE nrb_db;

CREATE TABLE DISTRICT (
    DistrictID   INT AUTO_INCREMENT PRIMARY KEY,
    DistrictName VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE ADDRESS (
    AddressID   INT AUTO_INCREMENT PRIMARY KEY,
    HouseNumber VARCHAR(50),
    Street      VARCHAR(60),
    Village     VARCHAR(60),
    Area        VARCHAR(60)
) ENGINE=InnoDB;

CREATE TABLE REGISTRATION_OFFICE (
    OfficeID       INT AUTO_INCREMENT PRIMARY KEY,
    OfficeName     VARCHAR(80) NOT NULL,
    OfficeLocation VARCHAR(100) NOT NULL,
    DistrictID     INT NOT NULL,
    CONSTRAINT fk_office_district FOREIGN KEY (DistrictID) REFERENCES DISTRICT(DistrictID)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE OFFICER (
    OfficerID  INT AUTO_INCREMENT PRIMARY KEY,
    FirstName  VARCHAR(50) NOT NULL,
    LastName   VARCHAR(50) NOT NULL,
    Position   VARCHAR(50) NOT NULL,
    OfficeID   INT NOT NULL,
    CONSTRAINT fk_officer_office FOREIGN KEY (OfficeID) REFERENCES REGISTRATION_OFFICE(OfficeID)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE PERSON (
    PersonID    INT AUTO_INCREMENT PRIMARY KEY,
    Surname     VARCHAR(50) NOT NULL,
    OtherNames  VARCHAR(80) NOT NULL,
    Sex         CHAR(1)     NOT NULL,
    DateOfBirth DATE        NOT NULL,
    Gender      VARCHAR(20) NOT NULL,
    Nationality VARCHAR(50) NOT NULL,
    PersonType  VARCHAR(10) NOT NULL,
    AddressID   INT,
    CONSTRAINT chk_person_type CHECK (PersonType IN ('CITIZEN','RESIDENT','ALIEN','REFUGEE')),
    CONSTRAINT chk_person_sex  CHECK (Sex IN ('M','F')),
    CONSTRAINT fk_person_address FOREIGN KEY (AddressID) REFERENCES ADDRESS(AddressID)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE CITIZEN (
    PersonID         INT PRIMARY KEY,
    IdentificationNo VARCHAR(20) NOT NULL UNIQUE,
    CONSTRAINT fk_citizen_person FOREIGN KEY (PersonID) REFERENCES PERSON(PersonID)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE RESIDENT (
    PersonID          INT PRIMARY KEY,
    ResidencePermitNo VARCHAR(30) NOT NULL UNIQUE,
    CONSTRAINT fk_resident_person FOREIGN KEY (PersonID) REFERENCES PERSON(PersonID)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE ALIEN (
    PersonID       INT PRIMARY KEY,
    PassportNumber VARCHAR(30) NOT NULL UNIQUE,
    CONSTRAINT fk_alien_person FOREIGN KEY (PersonID) REFERENCES PERSON(PersonID)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE REFUGEE (
    PersonID      INT PRIMARY KEY,
    RefugeeNumber VARCHAR(30) NOT NULL UNIQUE,
    CONSTRAINT fk_refugee_person FOREIGN KEY (PersonID) REFERENCES PERSON(PersonID)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE BIOMETRIC (
    BiometricID  INT AUTO_INCREMENT PRIMARY KEY,
    Fingerprint  BLOB,
    Photo        BLOB,
    Signature    BLOB,
    DateCaptured DATETIME NOT NULL,
    PersonID     INT NOT NULL,
    CONSTRAINT fk_biometric_person FOREIGN KEY (PersonID) REFERENCES PERSON(PersonID)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE APPLICATION (
    ApplicationID     INT AUTO_INCREMENT PRIMARY KEY,
    ApplicationDate   DATE        NOT NULL,
    ApplicationStatus VARCHAR(20) NOT NULL,
    ApplicationType   VARCHAR(20) NOT NULL,
    PersonID          INT NOT NULL,
    OfficerID         INT NOT NULL,
    OfficeID          INT NOT NULL,
    CONSTRAINT chk_app_status CHECK (ApplicationStatus IN ('Pending','Approved','Rejected')),
    CONSTRAINT fk_app_person  FOREIGN KEY (PersonID)  REFERENCES PERSON(PersonID) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_app_officer FOREIGN KEY (OfficerID) REFERENCES OFFICER(OfficerID) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_app_office  FOREIGN KEY (OfficeID)  REFERENCES REGISTRATION_OFFICE(OfficeID) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE ID_CARD (
    CardID           INT AUTO_INCREMENT PRIMARY KEY,
    IdentificationNo VARCHAR(50) NOT NULL UNIQUE,
    DateOfIssue      DATE NOT NULL,
    DateOfExpiry     DATE NOT NULL,
    CardStatus       VARCHAR(20) NOT NULL,
    QRCode           VARCHAR(255),
    ApplicationID    INT NOT NULL UNIQUE,
    CONSTRAINT chk_card_status CHECK (CardStatus IN ('Active','Expired','Revoked')),
    CONSTRAINT fk_card_application FOREIGN KEY (ApplicationID) REFERENCES APPLICATION(ApplicationID)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE GUARDIAN (
    PersonID       INT NOT NULL,
    GuardianSeqNo  INT NOT NULL,
    GuardianName   VARCHAR(100) NOT NULL,
    Relationship   VARCHAR(50) NOT NULL,
    ContactNumber  VARCHAR(20),
    PRIMARY KEY (PersonID, GuardianSeqNo),
    CONSTRAINT fk_guardian_person FOREIGN KEY (PersonID) REFERENCES PERSON(PersonID) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO DISTRICT (DistrictName) VALUES 
('Thyolo'), ('Neno'), ('Mwanza'), ('Lilongwe'), ('Dedza'), ('Blantyre'), ('Mzimba');

INSERT INTO ADDRESS (HouseNumber, Street, Village, Area) VALUES
('12A', 'Main Road', 'Bvumbwe', 'Thyolo'),
('8B', 'Market Road', 'Neno Center', 'Neno'),
('45', 'Border Road', 'Mwanza Boma', 'Mwanza'),
('3C', 'City Centre', 'Area 47', 'Lilongwe'),
('77', 'M1 Road', 'Dedza Boma', 'Dedza'),
('99', 'Limbe Road', 'Chilomoni', 'Blantyre'),
('22', 'Mzuzu Road', 'Katoto', 'Mzimba');

INSERT INTO REGISTRATION_OFFICE (OfficeName, OfficeLocation, DistrictID) VALUES
('NRB Thyolo Office', 'Thyolo Boma', 1),
('NRB Lilongwe HQ', 'City Centre', 4),
('NRB Blantyre Office', 'Limbe', 6);

INSERT INTO OFFICER (FirstName, LastName, Position, OfficeID) VALUES
('James', 'Banda', 'Senior Officer', 1),
('Mary', 'Phiri', 'Registration Officer', 2),
('Peter', 'Mwale', 'Registration Officer', 3);

INSERT INTO PERSON (Surname, OtherNames, Sex, DateOfBirth, Gender, Nationality, PersonType, AddressID) VALUES
('Kubwenze', 'Gilbert', 'M', '1990-05-14', 'Male', 'Malawian', 'CITIZEN', 1),
('Phiri', 'Emily', 'F', '1985-11-22', 'Female', 'Malawian', 'CITIZEN', 2),
('Zulu', 'Taonga', 'M', '1992-03-08', 'Male', 'Malawian', 'CITIZEN', 3),
('Chienda', 'Fraswel', 'F', '1998-07-19', 'Female', 'Malawian', 'CITIZEN', 4),
('Tambala', 'Martha', 'F', '1980-09-30', 'Female', 'Malawian', 'CITIZEN', 5),
('Manyonde', 'Gift', 'M', '1978-01-15', 'Male', 'British', 'RESIDENT', 6),
('Manuel', 'Isaac', 'M', '1989-06-05', 'Male', 'Zimbabwean', 'RESIDENT', 7),
('Muthali', 'Velocity', 'F', '1995-12-12', 'Female', 'Chinese', 'ALIEN', 1),
('Masangano', 'Noel', 'M', '1993-04-25', 'Male', 'Indian', 'ALIEN', 2),
('Majawa', 'Tamala', 'F', '1987-08-17', 'Female', 'Rwandan', 'REFUGEE', 3),
('Sichali', 'Richmond', 'M', '1991-02-14', 'Male', 'Malawian', 'CITIZEN', 4),
('Kainja', 'Ulemu', 'M', '1994-10-10', 'Male', 'Malawian', 'CITIZEN', 5),
('Kainja', 'Raphael', 'M', '1996-11-11', 'Male', 'Malawian', 'CITIZEN', 6);

INSERT INTO CITIZEN (PersonID, IdentificationNo) VALUES
(1, 'MWI-1000001'), (2, 'MWI-1000002'), (3, 'MWI-1000003'), 
(4, 'MWI-1000004'), (5, 'MWI-1000005'), (11, 'MWI-1000011'), 
(12, 'MWI-1000012'), (13, 'MWI-1000013');

INSERT INTO RESIDENT (PersonID, ResidencePermitNo) VALUES
(6, 'RES-2023-0001'), (7, 'RES-2023-0002');

INSERT INTO ALIEN (PersonID, PassportNumber) VALUES
(8, 'CN1234567'), (9, 'IN7654321');

INSERT INTO REFUGEE (PersonID, RefugeeNumber) VALUES
(10, 'REF-2024-001');

INSERT INTO BIOMETRIC (Fingerprint, Photo, Signature, DateCaptured, PersonID) VALUES
(NULL, NULL, NULL, '2024-01-15 09:30:00', 1),
(NULL, NULL, NULL, '2024-01-16 10:15:00', 2),
(NULL, NULL, NULL, '2024-02-20 11:45:00', 3),
(NULL, NULL, NULL, '2024-02-21 14:00:00', 4),
(NULL, NULL, NULL, '2024-03-10 08:30:00', 5),
(NULL, NULL, NULL, '2024-03-15 13:20:00', 6),
(NULL, NULL, NULL, '2024-04-01 10:10:00', 7),
(NULL, NULL, NULL, '2024-04-05 15:30:00', 8),
(NULL, NULL, NULL, '2024-04-10 09:45:00', 9),
(NULL, NULL, NULL, '2024-04-15 16:00:00', 10),
(NULL, NULL, NULL, '2024-04-16 11:00:00', 11),
(NULL, NULL, NULL, '2024-04-17 12:00:00', 12),
(NULL, NULL, NULL, '2024-04-18 13:00:00', 13);

INSERT INTO APPLICATION (ApplicationDate, ApplicationStatus, ApplicationType, PersonID, OfficerID, OfficeID) VALUES
('2024-01-15', 'Approved', 'New', 1, 1, 1),
('2024-01-16', 'Approved', 'New', 2, 1, 1),
('2024-02-20', 'Approved', 'New', 3, 2, 2),
('2024-02-21', 'Pending',  'New', 4, 2, 2),
('2024-03-10', 'Approved', 'New', 5, 3, 3),
('2024-03-15', 'Approved', 'New', 6, 3, 3),
('2024-04-01', 'Pending',  'New', 7, 1, 1),
('2024-04-05', 'Rejected', 'New', 8, 1, 1),
('2024-04-10', 'Approved', 'New', 9, 2, 2),
('2024-04-15', 'Approved', 'New', 10, 3, 3),
('2024-04-16', 'Approved', 'New', 11, 1, 1),
('2024-04-17', 'Approved', 'New', 12, 2, 2),
('2024-04-18', 'Pending',  'New', 13, 3, 3);

INSERT INTO ID_CARD (IdentificationNo, DateOfIssue, DateOfExpiry, CardStatus, QRCode, ApplicationID) VALUES
('MWI-1000001', '2024-01-20', '2034-01-20', 'Active', 'QR-001', 1),
('MWI-1000002', '2024-01-22', '2034-01-22', 'Active', 'QR-002', 2),
('MWI-1000003', '2024-02-25', '2034-02-25', 'Active', 'QR-003', 3),
('MWI-1000005', '2024-03-15', '2034-03-15', 'Active', 'QR-005', 5),
('RES-2023-0001', '2024-03-20', '2029-03-20', 'Active', 'QR-006', 6),
('IN7654321', '2024-04-15', '2029-04-15', 'Active', 'QR-009', 9),
('REF-2024-001', '2024-04-20', '2027-04-20', 'Active', 'QR-010', 10),
('MWI-1000011', '2024-04-21', '2034-04-21', 'Active', 'QR-011', 11),
('MWI-1000012', '2024-04-22', '2034-04-22', 'Active', 'QR-012', 12);