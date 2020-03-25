CREATE TABLE IF NOT EXISTS users (
    user_id UUID PRIMARY KEY,
    username TEXT UNIQUE NOT NULL,
    email TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL,
    first_name TEXT NOT NULL,
    last_name TEXT NOT NULL,
    age INTEGER,
    verification_hash TEXT,
    verified BOOLEAN,
    registration_date TIMESTAMP
);
CREATE TYPE visibility AS ENUM ('private', 'public');
CREATE TABLE IF NOT EXISTS events (
    event_id UUID PRIMARY KEY,
    creator_id UUID REFERENCES users (user_id),
    creation_date TIMESTAMP ,
    title TEXT,
    description TEXT,
    location TEXT,
    date DATE,
    time TIME,
    visibility visibility,
    maximum_attendees INTEGER,
    price INTEGER
);
CREATE TABLE IF NOT EXISTS bookings (
    booking_id UUID PRIMARY KEY,
    event_id UUID REFERENCES events (event_id),
    user_id UUID REFERENCES users (user_id),
    status TEXT,
    accepted_date TIMESTAMP
);
CREATE TABLE IF NOT EXISTS sessions (
    session_id UUID PRIMARY KEY,
    user_id UUID REFERENCES users (user_id),
    login_time TIME
);