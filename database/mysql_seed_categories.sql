-- Switch to the database
USE e_donate;

-- Seed data for categories
INSERT INTO categories (name, description) VALUES
('Microcontrollers and Boards', 'Various microcontroller boards including Arduino, Raspberry Pi, and other development boards'),
('Sensors and Modules', 'Different types of sensors and electronic modules for various applications'),
('Wires and Connectors', 'Various types of wires, jumper cables, and connectors for electronic projects'),
('Power Supply', 'Power supply units, batteries, and voltage regulators'),
('Display Screens', 'LCD, LED, and other types of display screens for electronic projects'),
('Prototyping Materials', 'Breadboards, perfboards, and other materials for prototyping'),
('Electronic Components', 'Basic electronic components like resistors, capacitors, and ICs'),
('Cables and Adapters', 'Various types of cables and adapters for electronic connections'),
('Past Projects', 'Completed or partially completed electronic projects available for donation'),
('Tools and Equipment', 'Tools and equipment used in electronic projects and repairs'); 