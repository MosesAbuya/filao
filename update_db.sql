USE filao_adventures;

-- Update Destinations Coordinates
UPDATE destinations SET latitude = -8.335, longitude = 115.088 WHERE name LIKE '%Bali%';
UPDATE destinations SET latitude = 25.2769, longitude = 55.2962 WHERE name LIKE '%Dubai%';
UPDATE destinations SET latitude = -20.0, longitude = 80.0 WHERE name LIKE '%Indian Ocean%';
UPDATE destinations SET latitude = 42.0, longitude = 12.0833 WHERE name LIKE '%Italy%';
UPDATE destinations SET latitude = 48.86, longitude = 2.35 WHERE name LIKE '%Paris%';
UPDATE destinations SET latitude = 36.415, longitude = 25.4325 WHERE name LIKE '%Santorini%';
UPDATE destinations SET latitude = 15.0, longitude = 100.0 WHERE name LIKE '%Thailand%';

-- Update Itinerary Steps Accommodations
-- IDs for accommodations:
-- 1: Mara Serena Safari Lodge
-- 2: Amboseli Serena Safari Lodge
-- 3: Neptune Mara Rianta
-- 4: Diani Reef Beach Resort & Spa
-- 5: Ole Sereni Nairobi
-- 6: Amboseli Sopa Lodge
-- 7: Kilaguni Serena Safari Lodge
-- 8: Salt Lick Safari Lodge
-- 9: PrideInn Westlands Nairobi
-- 10: Kibo Safari Camp Amboseli
-- 11: PrideInn Mara Camp
-- 12: Mara Sopa Lodge
-- 13: Lake Nakuru Sopa Lodge

-- 1. 4-Day Nairobi – Masai Mara Group Safari (Tour 3)
UPDATE itinerary_steps SET accommodation_id = 9 WHERE tour_id = 3 AND step_title LIKE '%Nairobi%';
UPDATE itinerary_steps SET accommodation_id = 11 WHERE tour_id = 3 AND step_title LIKE '%Mara%';

-- 2. 4-Day Nairobi - Amboseli Safari (Tour 2)
UPDATE itinerary_steps SET accommodation_id = 9 WHERE tour_id = 2 AND step_title LIKE '%Nairobi%';
UPDATE itinerary_steps SET accommodation_id = 10 WHERE tour_id = 2 AND step_title LIKE '%Amboseli%';

-- 3. 5-Day Masai Mara – Lake Nakuru Safari (Tour 4)
UPDATE itinerary_steps SET accommodation_id = 9 WHERE tour_id = 4 AND step_title LIKE '%Nairobi%';
UPDATE itinerary_steps SET accommodation_id = 12 WHERE tour_id = 4 AND step_title LIKE '%Mara%';
UPDATE itinerary_steps SET accommodation_id = 13 WHERE tour_id = 4 AND step_title LIKE '%Nakuru%';

-- 4. 7-Day Masai Mara – Lake Nakuru – Amboseli Safari (Tour 5)
UPDATE itinerary_steps SET accommodation_id = 9 WHERE tour_id = 5 AND step_title LIKE '%Nairobi%';
UPDATE itinerary_steps SET accommodation_id = 12 WHERE tour_id = 5 AND step_title LIKE '%Mara%';
UPDATE itinerary_steps SET accommodation_id = 13 WHERE tour_id = 5 AND step_title LIKE '%Nakuru%';
UPDATE itinerary_steps SET accommodation_id = 6 WHERE tour_id = 5 AND step_title LIKE '%Amboseli%';

-- 5. 4-Day Amboseli & Tsavo Safari (Tour 1)
-- Wait, Tour 1 might not have Nairobi as step 1. Let's see: Amboseli Sopa Lodge (1 Night), Kilaguni Serena Safari Lodge (1 Night), Salt Lick Safari Lodge (1 Night)
UPDATE itinerary_steps SET accommodation_id = 6 WHERE tour_id = 1 AND step_title LIKE '%Amboseli%';
UPDATE itinerary_steps SET accommodation_id = 7 WHERE tour_id = 1 AND step_title LIKE '%Tsavo%';
UPDATE itinerary_steps SET accommodation_id = 8 WHERE tour_id = 1 AND step_title LIKE '%Taita%';
