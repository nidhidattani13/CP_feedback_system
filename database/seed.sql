-- Fixed feedback questions
INSERT INTO questions (question_text, question_type) VALUES 
('Was the faculty present?', 'yesno'),
('Was the faculty on time?', 'mcq'),
('Lecture delivery effectiveness?', 'rating'),
('Was homework given?', 'yesno'),
('Topic name for homework (if yes)', 'text'),
('Use of proper tools for teaching?', 'yesno'),
('Any feedback?', 'text');

-- Options for MCQ and rating
INSERT INTO question_options (question_id, option_text) VALUES
(2, 'Yes'),
(2, 'No'),
(2, 'Late by 5 min'),
(2, 'Late by 10 min'),
(2, 'Late by 15 min'),
(3, 'Excellent'),
(3, 'Very Good'),
(3, 'Good'),
(3, 'Bad');
INSERT INTO questions (question_text, question_type) VALUES
('Was the faculty present?', 'yesno'),
('Was the faculty on time?', 'mcq'),
('How was the teaching content quality?', 'mcq'),
('Did the faculty use proper tools/software to teach?', 'yesno'),
('Did the faculty teach mostly about the subject?', 'yesno'),
('Lecture delivery effectiveness?', 'mcq');

-- Options for MCQ questions
INSERT INTO question_options (question_id, option_text) VALUES
(2, 'On time'), (2, '5 min late'), (2, '10 min late'), (2, '15+ min late'),
(3, 'Excellent'), (3, 'Good'), (3, 'Bad'), (3, 'Very Bad'),
(6, 'Excellent'), (6, 'Very Good'), (6, 'Good'), (6, 'Bad');
