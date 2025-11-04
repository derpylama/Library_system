INSERT INTO category (sab_code, name) VALUES
('A', 'Bok- och biblioteksväsen'),
('B', 'Allmänt och blandat'),
('C', 'Religion'),
('D', 'Filosofi och psykologi'),
('E', 'Uppfostran och undervisning'),
('F', 'Språkvetenskap'),
('G', 'Litteraturvetenskap'),
('H', 'Skönlitteratur'),
('I', 'Konst, musik, teater, film och fotokonst'),
('J', 'Arkeologi'),
('K', 'Historia'),
('L', 'Biografi med genealogi'),
('M', 'Etnografi, socialantropologi och etnologi'),
('N', 'Geografi och lokalhistoria'),
('O', 'Samhälls- och rättsvetenskap'),
('P', 'Teknik, industri och kommunikationer'),
('Q', 'Ekonomi och näringsväsen'),
('R', 'Idrott, lek och spel'),
('S', 'Militärväsen'),
('T', 'Matematik'),
('U', 'Naturvetenskap'),
('V', 'Medicin'),
('X', 'Musikalier'),
('Y', 'Musikinspelningar'),
('A_', 'Tidningar'); -- "A_" is "Ä"


INSERT INTO user (username, password_, is_admin, created_at) VALUES
('admin', 'admin123', 1, '2025-10-30 17:53:11'),
('alice', 'password1', 0, '2025-10-30 17:53:11'),
('bob', 'password2', 0, '2025-10-30 17:53:11'),
('person2', 'person2', 0, '2025-10-30 17:55:57');


INSERT INTO media (isbn, title, author, media_type, category_id, description, price, created_at, updated_at, barcode) VALUES
('9780261103573', 'Sagan om ringen', 'J.R.R. Tolkien', 'bok', 7, 'En episk fantasyroman.', 299.00, '2025-10-30 17:53:11', '2025-10-30 17:53:11', 'SAGAN'),
('9789170018361', 'Män som hatar kvinnor', 'Stieg Larsson', 'bok', 7, 'Första delen i Millennium-trilogin.', 159.00, '2025-10-30 17:53:11', '2025-10-30 17:53:11', 'MANSO'),
('9789129688310', 'Pippi Långstrump', 'Astrid Lindgren', 'ljudbok', 7, 'Klassisk barnbok som ljudbok.', 120.00, '2025-10-30 17:53:11', '2025-10-30 17:53:11', 'PIPPI'),
('9780747532743', 'Harry Potter och de vises sten', ' J.K. Rowling', 'bok', 7, 'Magisk fantasyroman för alla åldrar.', 199.00, '2025-10-30 17:53:11', '2025-10-30 17:53:11', 'HARRY'),
('9780307887443', 'The Martian', 'Andy Weir', 'bok', 7, 'Sci-fi berättelse om en man strandad på Mars.', 179.00, '2025-10-30 17:53:11', '2025-10-30 17:53:11', 'THEMA'),
('9780000000003', 'Star Wars: A New Hope', 'George Lucas', 'film', 8, 'Klassisk sci-fi film.', 249.00, '2025-10-30 17:53:11', '2025-10-30 17:53:11', 'STARW'),
('9780000000004', 'Interstellar', 'Christopher Nolan', 'film', 8, 'Ett mästerverk inom science fiction.', 269.00, '2025-10-30 17:53:11', '2025-10-30 17:53:11', 'INTER'),
('9780001831803', 'The Lion, the Witch and the Wardrobe', 'C.S. Lewis', 'bok', 7, 'Adventures in Narnia', 150.00, '2025-11-02 22:32:51', '2025-11-02 22:32:51', 'THELI');


INSERT INTO copy (media_id, barcode, status, created_at) VALUES
(1, '001', 'available', '2025-10-30 17:53:11'),
(1, '002', 'available', '2025-10-30 17:53:11'),
(2, '001', 'on_loan', '2025-10-30 17:53:11'),
(3, '001', 'available', '2025-10-30 17:53:11'),
(4, '001', 'on_loan', '2025-10-30 17:53:11'),
(5, '001', 'available', '2025-10-30 17:53:11'),
(5, '002', 'available', '2025-10-30 17:53:11'),
(6, '001', 'written_off', '2025-10-30 17:53:11'),
(7, '001', 'available', '2025-10-30 17:53:11'),
(8, '001', 'on_loan', '2025-11-02 22:39:25');


INSERT INTO loan (copy_id, user_id, loan_date, due_date, return_date, status) VALUES
(3, 2, '2025-10-30', '2025-11-20', NULL, 'active'),
(8, 3, '2025-09-30', '2025-10-21', NULL, 'written_off'),
(5, 2, '2025-10-30', '2025-11-20', NULL, 'active'),
(1, 3, '2025-11-01', '2025-11-22', '2025-11-01', 'returned'),
(8, 1, '2025-11-02', '2025-11-23', NULL, 'active');


INSERT INTO invoice (user_id, loan_id, amount, issued_at, paid, description) VALUES
(3, 2, 373.50, '2025-10-30 17:53:11', 0, 'Overdue fine for Star Wars');