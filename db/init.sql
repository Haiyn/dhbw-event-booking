CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE TABLE IF NOT EXISTS users (
    user_id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    username TEXT UNIQUE NOT NULL,
    email TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL,
    first_name TEXT,
    last_name TEXT,
    age INTEGER,
    verification_hash TEXT,
    verified BOOLEAN,
    registration_date TIMESTAMP DEFAULT NOW()
);
CREATE TYPE visibility AS ENUM ('invite-only', 'public');
CREATE TABLE IF NOT EXISTS events (
    event_id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    creator_id UUID REFERENCES users (user_id),
    creation_date TIMESTAMP DEFAULT NOW(),
    title TEXT,
    description TEXT,
    location TEXT,
    date DATE,
    time TIME,
    visibility visibility,
    maximum_attendees INTEGER,
    price NUMERIC (8, 2)
);
CREATE TYPE status AS ENUM ('invited', 'accepted');
CREATE TABLE IF NOT EXISTS bookings (
    booking_id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    event_id UUID REFERENCES events (event_id),
    user_id UUID REFERENCES users (user_id),
    status status,
    accepted_date TIMESTAMP
);
CREATE TABLE IF NOT EXISTS sessions (
    session_id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID REFERENCES users (user_id),
    login_time TIME,
    ip_address TEXT,
    user_agent TEXT
);