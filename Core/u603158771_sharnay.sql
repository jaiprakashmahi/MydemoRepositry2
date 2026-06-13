-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 13, 2026 at 01:34 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u603158771_sharnay`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `email`, `password`) VALUES
(1, 'admin@gmail.com', '$2y$10$oc8wiJLiq6.kvkKAZBjEfOQ8R74l.8BReN/fQr6JYYImdR.Vn.M36');

-- --------------------------------------------------------

--
-- Table structure for table `blocks`
--

CREATE TABLE `blocks` (
  `id` int(11) NOT NULL,
  `district_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `blocks`
--

INSERT INTO `blocks` (`id`, `district_id`, `name`) VALUES
(1, 90, 'Islampur'),
(2, 96, 'Ujiyarpur'),
(3, 96, 'Sarairanjan'),
(4, 96, 'Morwa'),
(5, 96, 'Samastipur'),
(6, 96, 'Tajpur'),
(7, 96, 'Khanpur'),
(8, 96, 'Pusa'),
(9, 96, 'Warishnagar'),
(10, 96, 'Kalyanpur'),
(11, 96, 'Rosera'),
(12, 96, 'Hasanpur'),
(13, 96, 'Bithan'),
(14, 96, 'Shivajinagar'),
(15, 96, 'Singhiya'),
(16, 96, 'Bibhutipur'),
(17, 96, 'Dalsinghsarai'),
(18, 96, 'Vidyapatinagar'),
(19, 96, 'Patori'),
(20, 96, 'Mohanpur'),
(21, 96, 'Mohiuddin Nagar'),
(22, 71, 'Begusarai'),
(23, 71, 'Barauni'),
(24, 71, 'Teghra'),
(25, 71, 'Matihani'),
(26, 71, 'Bachhwara'),
(27, 71, 'Mansurchak'),
(28, 71, 'Naokothi'),
(29, 71, 'Cheriabariyarpur'),
(30, 71, 'Sahebpur Kamal'),
(31, 71, 'Bakhari'),
(32, 71, 'Birpur'),
(33, 71, 'Dandari'),
(34, 71, 'Garhpura'),
(35, 71, 'Balia'),
(36, 71, 'Chhorahi'),
(37, 71, 'Khodawandpur'),
(38, 71, 'Bhagwanpur'),
(39, 71, 'Samho Akha Kurha'),
(40, 89, 'AURAI'),
(41, 89, 'BANDRA'),
(42, 89, 'BOCHAHAN'),
(43, 89, 'GAIGHAT'),
(44, 89, 'MUSHAHARI'),
(45, 89, 'KATRA'),
(46, 89, 'MINAPUR'),
(47, 89, 'MURAUL'),
(48, 89, 'SAKRA'),
(49, 89, 'KANTI'),
(50, 89, 'KURHANI'),
(51, 89, 'MARWAN'),
(52, 89, 'PAROO'),
(53, 89, 'SAHEBGANJ'),
(54, 89, 'MOTIPUR'),
(55, 89, 'SARAIYA'),
(56, 103, 'Bhagwanpur'),
(57, 103, 'Bidupur'),
(58, 103, 'Chehrakala'),
(59, 103, 'Desari'),
(60, 103, 'Goraul'),
(61, 103, 'Hajipur'),
(62, 103, 'Jandaha'),
(63, 103, 'Lalganj'),
(64, 103, 'Mahnar'),
(65, 103, 'Mahua'),
(66, 103, 'Patedhi Belsar'),
(67, 103, 'Patepur'),
(68, 103, 'Raghopur'),
(69, 103, 'Rajapakar'),
(70, 103, 'Sahdei Buzurg'),
(71, 103, 'Vaishali'),
(72, 75, 'Darbhanga Sadar'),
(73, 75, 'Bahadurpur'),
(74, 75, 'Baheri'),
(75, 75, 'Hayaghat'),
(76, 75, 'Hanuman Nagar'),
(77, 75, 'Jale'),
(78, 75, 'Singhwara'),
(79, 75, 'Keoti'),
(80, 75, 'Manigachhi'),
(81, 75, 'Tardih'),
(82, 75, 'Benipur'),
(83, 75, 'Alinagar'),
(84, 75, 'Biraul'),
(85, 75, 'Gaura Bauram'),
(86, 75, 'Kiratpur'),
(87, 75, 'Ghanshyampur'),
(88, 75, 'Kusheshwar Asthan'),
(89, 75, 'Kusheshwar Asthan East'),
(90, 90, 'Asthawan'),
(91, 90, 'Ben'),
(92, 90, 'Biharsharif'),
(93, 90, 'Bind'),
(94, 90, 'Chandi'),
(95, 90, 'Ekangarsarai'),
(96, 90, 'Giriyak (Giriak)'),
(97, 90, 'Harnaut'),
(98, 90, 'Hilsa'),
(99, 90, 'Karai Parsarai (Karaiparsurai)'),
(100, 90, 'Katrisarai'),
(101, 90, 'Nagarnausa'),
(102, 90, 'Noorsarai (Nursarai)'),
(103, 90, 'Parwalpur (Parbalpur)'),
(104, 90, 'Rahui'),
(105, 90, 'Rajgir'),
(106, 90, 'Sarmera'),
(107, 90, 'Silao'),
(108, 90, 'Tharthari'),
(109, 70, 'Banka'),
(110, 70, 'Amarpur'),
(111, 70, 'Shambhuganj'),
(112, 70, 'Belhar'),
(113, 70, 'Fullidumar'),
(114, 70, 'Katoria'),
(115, 70, 'Chandan'),
(116, 70, 'Baunsi'),
(117, 70, 'Barahat'),
(118, 70, 'Dhoraiya'),
(119, 70, 'Rajoun'),
(120, 72, 'Narayanpur'),
(121, 72, 'Bihpur'),
(122, 72, 'Kharik'),
(123, 72, 'Naugachia'),
(124, 72, 'Rangra Chowk'),
(125, 72, 'Gopalpur'),
(126, 72, 'Ismailpur'),
(127, 72, 'Pirpainty'),
(128, 72, 'Kahalgaon'),
(129, 72, 'Sanhoula'),
(130, 70, 'Sabour'),
(131, 72, 'Sabour'),
(132, 72, 'Nathnagar'),
(133, 72, 'Sultanganj'),
(134, 72, 'Shahkund'),
(135, 72, 'Goradih'),
(136, 72, 'Jagdishpur'),
(137, 77, 'Amas'),
(138, 77, 'Atri'),
(139, 77, 'Bankey Bazar'),
(140, 77, 'Barachatti'),
(141, 77, 'Belaganj'),
(142, 77, 'Bodhgaya'),
(143, 77, 'Dobhi'),
(144, 77, 'Dumaria'),
(145, 77, 'Fatehpur'),
(146, 77, 'Gaya Town'),
(147, 77, 'Guraru'),
(148, 77, 'Gurua'),
(149, 77, 'Imamganj'),
(150, 77, 'Khizarsarai'),
(151, 77, 'Konch'),
(152, 77, 'Manpur'),
(153, 77, 'Mohanpur'),
(154, 77, 'Mohra'),
(155, 77, 'Neemchak Bathani'),
(156, 77, 'Paraiya'),
(157, 77, 'Sherghati'),
(158, 77, 'Tankuppa'),
(159, 77, 'Tekari'),
(160, 77, 'Wazirganj'),
(161, 88, 'Asarganj'),
(162, 88, 'Bariyarpur (Bariarpur)'),
(163, 88, 'Dharhara'),
(164, 88, 'Jamalpur'),
(165, 88, 'Kharagpur'),
(166, 88, 'Munger Sadar'),
(167, 88, 'Sangrampur'),
(168, 88, 'Tarapur'),
(169, 88, 'Tetiabambar'),
(170, 92, 'Athmalgola'),
(171, 92, 'Bakhtiyarpur'),
(172, 92, 'Barh'),
(173, 92, 'Belchhi'),
(174, 92, 'Bihta'),
(175, 92, 'Bikram'),
(176, 92, 'Daniyawan'),
(177, 92, 'Dhanarua'),
(178, 92, 'Danapur'),
(179, 92, 'Dulhinbazar'),
(180, 92, 'Fatuha'),
(181, 92, 'Ghoswari'),
(182, 92, 'Khusrupur'),
(183, 92, 'Maner'),
(184, 92, 'Masaurhi'),
(185, 92, 'Mokama'),
(186, 92, 'Naubatpur'),
(187, 92, 'Paliganj'),
(188, 92, 'Pandarak'),
(189, 92, 'Patna Sadar'),
(190, 92, 'Phulwari Sharif'),
(191, 92, 'Punpun'),
(192, 92, 'Sampatchak'),
(193, 67, 'Araria'),
(194, 67, 'Jokihat'),
(195, 67, 'Kursakanta'),
(196, 67, 'Raniganj'),
(197, 67, 'Sikti'),
(198, 67, 'Palasi'),
(199, 67, 'Forbesganj'),
(200, 67, 'Narpatganj'),
(201, 67, 'Bhargama'),
(202, 68, 'Arwal'),
(203, 68, 'Kaler'),
(204, 68, 'Karpi'),
(205, 68, 'Kurtha'),
(206, 68, 'Suryapur Vanshi'),
(207, 69, 'Aurangabad'),
(208, 69, 'Barun'),
(209, 69, 'Daudnagar'),
(210, 69, 'Deo'),
(211, 69, 'Goh'),
(212, 69, 'Haspura'),
(213, 69, 'Kutumba'),
(214, 69, 'Madanpur'),
(215, 69, 'Nabinagar'),
(216, 69, 'Obra'),
(217, 69, 'Rafiganj'),
(218, 73, 'Arrah (Arah/Ara Sadar)'),
(219, 73, 'Barhara'),
(220, 73, 'Charpokhari'),
(221, 73, 'Garhani'),
(222, 73, 'Jagdishpur'),
(223, 73, 'Koilwar'),
(224, 73, 'Piro'),
(225, 73, 'Sahar'),
(226, 73, 'Shahpur'),
(227, 73, 'Tarari'),
(228, 73, 'Udwantnagar'),
(229, 73, 'Sandesh'),
(230, 73, 'Bhojpur'),
(231, 73, 'Dhanarua'),
(232, 74, 'Buxar'),
(233, 74, 'Itarhi'),
(234, 74, 'Chausa'),
(235, 74, 'Rajpur'),
(236, 74, 'Dumraon'),
(237, 74, 'Nawanagar'),
(238, 74, 'Brahampur'),
(239, 74, 'Kesath'),
(240, 74, 'Chakki'),
(241, 74, 'Chougain'),
(242, 74, 'Simri'),
(243, 76, 'Sangrampur'),
(244, 76, 'Harsidhi'),
(245, 76, 'Areraj'),
(246, 76, 'Paharpur'),
(247, 76, 'Chakia'),
(248, 76, 'Mehsi'),
(249, 76, 'Kesaria'),
(250, 76, 'Kalyanpur'),
(251, 76, 'Banjaria'),
(252, 76, 'Motihari'),
(253, 76, 'Piprakothi'),
(254, 76, 'Kotowa'),
(255, 76, 'Sugauli'),
(256, 76, 'Turkaulia'),
(257, 76, 'Madhuban'),
(258, 76, 'Tetaria'),
(259, 76, 'Patahi'),
(260, 76, 'Phenhara'),
(261, 76, 'Pakaridayal'),
(262, 76, 'Raxaul'),
(263, 76, 'Ramgarhwa'),
(264, 76, 'Adapur'),
(265, 76, 'Chauradano'),
(266, 76, 'Bankatwa'),
(267, 76, 'Chiraiya'),
(268, 76, 'Ghorasahan'),
(269, 76, 'Dhaka'),
(270, 78, 'Baikunthpur'),
(271, 78, 'Barauli'),
(272, 78, 'Bhorey'),
(273, 78, 'Bijaipur'),
(274, 78, 'Kateya'),
(275, 78, 'Kuchaikote'),
(276, 78, 'Manjhagarh'),
(277, 78, 'Gopalganj'),
(278, 78, 'Sidhwalia'),
(279, 78, 'Uchkagaon'),
(280, 78, 'Thawe'),
(281, 78, 'Phulwariya'),
(282, 78, 'Hathua'),
(283, 78, 'Panchdeori'),
(284, 79, 'Barhat'),
(285, 79, 'Chakai'),
(286, 79, 'Gidhaur'),
(287, 79, 'Islamnagar Aliganj'),
(288, 79, 'Jamui'),
(289, 79, 'Jhajha'),
(290, 79, 'Khaira'),
(291, 79, 'Laxmipur'),
(292, 79, 'Sikandra'),
(293, 79, 'Sono'),
(294, 80, 'Ghoshi'),
(295, 80, 'Hulasganj'),
(296, 80, 'Kako'),
(297, 80, 'Makhdumpur'),
(298, 80, 'Modanganj'),
(299, 80, 'Ratni Faridpur'),
(300, 80, 'Jehanabad'),
(301, 81, 'Adhaura'),
(302, 81, 'Bhabua'),
(303, 81, 'Bhagwanpur'),
(304, 81, 'Chainpur'),
(305, 81, 'Chand'),
(306, 81, 'Durgawati'),
(307, 81, 'Kudra'),
(308, 81, 'Mohania'),
(309, 81, 'Ramgarh'),
(310, 81, 'Rampur'),
(311, 81, 'Sonhan'),
(312, 82, 'Azamnagar'),
(313, 82, 'Balrampur'),
(314, 82, 'Barari'),
(315, 82, 'Barsoi'),
(316, 82, 'Falka'),
(317, 82, 'Hasanganj'),
(318, 82, 'Kadwa'),
(319, 82, 'Katihar'),
(320, 82, 'Korha'),
(321, 82, 'Kursela'),
(322, 82, 'Manihari'),
(323, 82, 'Mansahi'),
(324, 82, 'Mihma'),
(325, 82, 'Pranpur'),
(326, 82, 'Sameli'),
(327, 82, 'Semapur'),
(328, 83, 'Alauli'),
(329, 83, 'Khagaria'),
(330, 83, 'Chautham'),
(331, 83, 'Beldaur'),
(332, 83, 'Gogri'),
(333, 83, 'Parbatta'),
(334, 83, 'Madafarpur'),
(335, 85, 'Lakhisarai'),
(336, 85, 'Surajgarha'),
(337, 85, 'Barahiya'),
(338, 85, 'Halsi'),
(339, 85, 'Pipariya'),
(340, 85, 'Ramgarh Chowk'),
(341, 85, 'Chanan'),
(342, 86, 'Alamnagar'),
(343, 86, 'Bihariganj'),
(344, 86, 'Chausa'),
(345, 86, 'Gamhariya'),
(346, 86, 'Ghelardh/Ghailarh'),
(347, 86, 'Gwalpara'),
(348, 86, 'Kumarkhand'),
(349, 86, 'Madhepura'),
(350, 86, 'Murliganj'),
(351, 86, 'Puraini'),
(352, 86, 'Shankarpur'),
(353, 86, 'Singheshwar'),
(354, 86, 'Udakishunganj'),
(355, 99, 'Sheohar'),
(356, 99, 'Piprarhi'),
(357, 99, 'Purnahiya'),
(358, 99, 'Dumri Katsari'),
(359, 99, 'Tariyani'),
(360, 84, 'Kishanganj'),
(361, 84, 'Bahadurganj'),
(362, 84, 'Thakurganj'),
(363, 84, 'Kochadhaman'),
(364, 84, 'Pothia'),
(365, 84, 'Terhagachh'),
(366, 84, 'Dighalbank'),
(367, 94, 'Akorhi Gola'),
(368, 94, 'Bikramganj'),
(369, 94, 'Chenari'),
(370, 94, 'Dawath'),
(371, 94, 'Dehri'),
(372, 94, 'Dinara'),
(373, 94, 'Karakat'),
(374, 94, 'Kochas'),
(375, 94, 'Nasriganj'),
(376, 94, 'Nauhatta'),
(377, 94, 'Nokha'),
(378, 94, 'Rajpur'),
(379, 94, 'Rohtas'),
(380, 94, 'Sanjhauli'),
(381, 94, 'Sasaram'),
(382, 94, 'Sheosagar (Shivsagar)'),
(383, 94, 'Suryapura'),
(384, 94, 'Tilouthu'),
(385, 98, 'Ariari'),
(386, 98, 'Barbigha'),
(387, 98, 'Chewara'),
(388, 98, 'Ghatkusumbha'),
(389, 98, 'Sheikhpura'),
(390, 98, 'Shekhopur Sarai'),
(391, 97, 'Amnour'),
(392, 97, 'Baniapur'),
(393, 97, 'Chapra'),
(394, 97, 'Dariapur'),
(395, 97, 'Dighwara'),
(396, 97, 'Ekma'),
(397, 97, 'Garkha'),
(398, 97, 'Ishuapur'),
(399, 97, 'Jalalpur'),
(400, 97, 'Lahladpur'),
(401, 97, 'Maker'),
(402, 97, 'Marhaura'),
(403, 97, 'Masrakh'),
(404, 97, 'Panapur'),
(405, 97, 'Parsa'),
(406, 97, 'Rivilganj'),
(407, 97, 'Sahajitpur'),
(408, 97, 'Sonepur'),
(409, 97, 'Taraiya'),
(410, 97, 'Nagra'),
(411, 102, 'Supaul'),
(412, 102, 'Kishanpur'),
(413, 102, 'Saraigarh-Bhaptiyahi'),
(414, 102, 'Pipra'),
(415, 102, 'Triveniganj'),
(416, 102, 'Raghopur'),
(417, 102, 'Chhatapur'),
(418, 102, 'Nirmali'),
(419, 102, 'Marauna'),
(420, 102, 'Basantpur'),
(421, 102, 'Pratapganj'),
(422, 101, 'Siwan'),
(423, 101, 'Mairwa'),
(424, 101, 'Darauli'),
(425, 101, 'Guthani'),
(426, 101, 'Hussainganj'),
(427, 101, 'Andar'),
(428, 101, 'Raghunathpur'),
(429, 101, 'Siswan'),
(430, 101, 'Barharia'),
(431, 101, 'Pachrukhi'),
(432, 101, 'Nautan'),
(433, 101, 'Maharajganj'),
(434, 101, 'Daraundha'),
(435, 101, 'Goreakothi'),
(436, 101, 'Basantpur'),
(437, 101, 'Bhagwanpur'),
(438, 101, 'Lakri Nabiganj'),
(439, 101, 'Hasanpura'),
(440, 101, 'Jiradei'),
(441, 100, 'Dumra'),
(442, 100, 'Runisaidpur'),
(443, 100, 'Parihar'),
(444, 100, 'Bathnaha'),
(445, 100, 'Sonbarsa'),
(446, 100, 'Bajpatti'),
(447, 100, 'Sursand'),
(448, 100, 'Riga'),
(449, 100, 'Nanpur'),
(450, 100, 'Pupri'),
(451, 100, 'Bairgania'),
(452, 100, 'Bokhara'),
(453, 100, 'Suppi'),
(454, 100, 'Belsand'),
(455, 100, 'Majorganj'),
(456, 100, 'Parsauni'),
(457, 100, 'Charaut'),
(458, 95, 'Kahara'),
(459, 95, 'Sattar Kataiya'),
(460, 95, 'Nauhatta'),
(461, 95, 'Mahishi'),
(462, 95, 'Sonbarsa'),
(463, 95, 'Sour Bazar'),
(464, 95, 'Patarghat'),
(465, 95, 'Simri Bakhtiyarpur'),
(466, 95, 'Salkhua'),
(467, 95, 'Banma Itahri'),
(468, 93, 'Amaur'),
(469, 93, 'Baisa'),
(470, 93, 'Baisi'),
(471, 93, 'Banmankhi'),
(472, 93, 'Barhara Kothi'),
(473, 93, 'Bhawanipur'),
(474, 93, 'Dagarua (Dagarwa)'),
(475, 93, 'Dhamdaha'),
(476, 93, 'East Purnea'),
(477, 93, 'Jalalgarh'),
(478, 93, 'Krityanand Nagar (K. Nagar)'),
(479, 93, 'Kasba'),
(480, 93, 'Rupauli'),
(481, 93, 'Srinagar'),
(482, 87, 'Andhratharh'),
(483, 87, 'Babubarhi'),
(484, 87, 'Basopatti'),
(485, 87, 'Benipatti'),
(486, 87, 'Bisfi'),
(487, 87, 'Ghoghardiha'),
(488, 87, 'Harlakhi'),
(489, 87, 'Jainagar'),
(490, 87, 'Jhanjharpur'),
(491, 87, 'Kaluahi'),
(492, 87, 'Khajauli'),
(493, 87, 'Ladania'),
(494, 87, 'Lakhnaur'),
(495, 87, 'Madhepur'),
(496, 87, 'Madhubani'),
(497, 87, 'Pandaul'),
(498, 87, 'Phulparas'),
(499, 87, 'Rahika'),
(500, 87, 'Rajnagar'),
(501, 87, 'Andhratharhi'),
(502, 104, 'Bettiah'),
(503, 104, 'Piprasi'),
(504, 104, 'Nautan'),
(505, 104, 'Bairiya'),
(506, 104, 'Majhaulia'),
(507, 104, 'Bhitaha'),
(508, 104, 'Lauriya'),
(509, 104, 'Chanpatia'),
(510, 104, 'Mainatand'),
(511, 104, 'Sikta'),
(512, 104, 'Yogapatti'),
(513, 104, 'Narkatiaganj'),
(514, 104, 'Gaunaha'),
(515, 104, 'Ramnagar'),
(516, 104, 'Bagaha 1'),
(517, 104, 'Bagaha 2'),
(518, 104, 'Thakaraha'),
(519, 104, 'Madhubani'),
(520, 91, 'Akbarpur'),
(521, 91, 'Gobindpur'),
(522, 91, 'Hisua'),
(523, 91, 'Kashichak'),
(524, 91, 'Kawakole'),
(525, 91, 'Mescaur'),
(526, 91, 'Nardiganj'),
(527, 91, 'Narhat'),
(528, 91, 'Nawada'),
(529, 91, 'Pakri Barawan'),
(530, 91, 'Rajauli'),
(531, 91, 'Roh'),
(532, 91, 'Sirdala'),
(533, 91, 'Warisaliganj'),
(534, 669, 'Alipurduar Sadar'),
(535, 670, 'Bankura Sadar'),
(536, 670, 'Khatra'),
(537, 670, 'Bishnupur'),
(538, 685, 'Asansol Sadar'),
(539, 685, 'Durgapur'),
(540, 687, 'Kalna'),
(541, 687, 'Katwa'),
(542, 687, 'Bardhaman Sadar North'),
(543, 687, 'Bardhaman Sadar South'),
(544, 671, 'Suri Sadar'),
(545, 671, 'Bolpur'),
(546, 671, 'Rampurhat'),
(547, 672, 'Cooch Behar Sadar'),
(548, 672, 'Dinhata'),
(549, 672, 'Mathabhanga'),
(550, 672, 'Mekhliganj'),
(551, 672, 'Tufanganj'),
(552, 674, 'Darjeeling Sadar'),
(553, 674, 'Kurseong'),
(554, 674, 'Siliguri'),
(555, 674, 'Mirik'),
(556, 673, 'Balurghat Sadar'),
(557, 673, 'Gangarampur'),
(558, 675, 'Chinsurah Sadar'),
(559, 675, 'Chandannagore'),
(560, 675, 'Srirampore'),
(561, 675, 'Arambagh'),
(562, 676, 'Howrah Sadar'),
(563, 676, 'Uluberia'),
(564, 677, 'Jalpaiguri Sadar'),
(565, 677, 'Malbazar'),
(566, 677, 'Dhupguri'),
(567, 678, 'Jhargram Sadar'),
(568, 680, 'Kolkata'),
(569, 679, 'Kalimpong Sadar'),
(570, 681, 'Chanchal'),
(571, 681, 'Malda Sadar'),
(572, 686, 'Kharagpur'),
(573, 686, 'Medinipur Sadar'),
(574, 686, 'Ghatal'),
(575, 688, 'Tamluk Sadar'),
(576, 688, 'Haldia'),
(577, 688, 'Egra'),
(578, 688, 'Contai'),
(579, 682, 'Barhampur Sadar'),
(580, 682, 'Domkol'),
(581, 682, 'Lalbag'),
(582, 682, 'Kandi'),
(583, 682, 'Jangipur'),
(584, 683, 'Krishnanagar'),
(585, 683, 'Kalyani'),
(586, 683, 'Ranaghat'),
(587, 683, 'Tehatta'),
(588, 684, 'Barrackpore'),
(589, 684, 'Barasat Sadar'),
(590, 684, 'Bangaon'),
(591, 684, 'Basirhat'),
(592, 684, 'Bidhannagar'),
(593, 690, 'Baruipur'),
(594, 690, 'Canning'),
(595, 690, 'Diamond Harbour'),
(596, 690, 'Kakdwip'),
(597, 690, 'Alipore Sadar'),
(598, 689, 'Purulia Sadar'),
(599, 689, 'Manbazar'),
(600, 689, 'Raghunathpur'),
(601, 689, 'Jhalda'),
(602, 691, 'Raiganj Sadar'),
(603, 691, 'Islampur');

-- --------------------------------------------------------

--
-- Table structure for table `center_details`
--

CREATE TABLE `center_details` (
  `id` int(11) NOT NULL,
  `center_name` varchar(255) NOT NULL,
  `owner_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `date_of_create` date NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `phone` varchar(15) NOT NULL,
  `state` varchar(100) NOT NULL,
  `district` varchar(100) NOT NULL,
  `block` varchar(100) DEFAULT NULL,
  `pincode` int(11) NOT NULL,
  `id_proof` varchar(255) DEFAULT NULL,
  `reg_amount` decimal(10,2) DEFAULT NULL,
  `payment_mode` varchar(50) DEFAULT NULL,
  `payment_status` varchar(50) NOT NULL DEFAULT 'Pending',
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `member_id` int(11) DEFAULT NULL,
  `created_by` varchar(50) DEFAULT 'member',
  `center_status` varchar(50) NOT NULL DEFAULT 'Active',
  `approval_remarks` text DEFAULT NULL,
  `center_code` varchar(10) NOT NULL,
  `session_token` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `center_details`
--

INSERT INTO `center_details` (`id`, `center_name`, `owner_name`, `email`, `date_of_create`, `photo`, `phone`, `state`, `district`, `block`, `pincode`, `id_proof`, `reg_amount`, `payment_mode`, `payment_status`, `username`, `password`, `member_id`, `created_by`, `center_status`, `approval_remarks`, `center_code`, `session_token`) VALUES
(3, 'SIET COMPUTER INSTITUTE, SATANPUR, UJIYARPUR (SAMASTIPUR)-848132', 'REETA KUMARI', 'sanjeetcomputer1@gmail.com', '2026-03-05', 'MUKESH KUMAR.jpeg', '9835580983', '4', '96', 'UJIYARPUR', 848132, 'MUKESH KUMAR.jpeg', 3500.00, 'Online', 'Paid', 'Reeta@123', '$2y$10$cXapxJW1Shi3HKGnlWGBbeuid7EBXM3VmuvOYL16rgXyNH19RIDlC', NULL, 'member', 'Active', NULL, 'SFCC0001', NULL),
(9, 'RKV IT SOLUTION', 'deepak kumar', 'Deepakkumar933041@gmail.com', '2026-05-18', 'ChatGPT Image May 18, 2026, 03_33_51 PM.png', '07526972793', '4', '92', NULL, 801303, 'kpl s3.jpg', 3500.00, NULL, 'Paid', 'deepak@123', '$2y$10$wpTL1iAT56Qj56vGQ83A4um8qApsQ7fIE.9bnqPehmCrQt6kPTp9O', NULL, 'member', 'Active', NULL, 'SFCC0002', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `center_payment_methods`
--

CREATE TABLE `center_payment_methods` (
  `id` int(11) NOT NULL,
  `center_id` int(11) NOT NULL,
  `method_type` enum('upi','debit_card','credit_card','bank_account') NOT NULL,
  `upi_id` varchar(100) DEFAULT NULL,
  `card_number` varchar(20) DEFAULT NULL,
  `card_holder_name` varchar(255) DEFAULT NULL,
  `expiry_date` varchar(10) DEFAULT NULL,
  `cvv` varchar(4) DEFAULT NULL,
  `bank_name` varchar(255) DEFAULT NULL,
  `account_number` varchar(30) DEFAULT NULL,
  `ifsc_code` varchar(20) DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `center_wallet`
--

CREATE TABLE `center_wallet` (
  `id` int(11) NOT NULL,
  `center_id` int(11) NOT NULL,
  `balance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `certificates`
--

CREATE TABLE `certificates` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `student_code` varchar(50) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `father_name` varchar(100) DEFAULT NULL,
  `mother_name` varchar(100) DEFAULT NULL,
  `course_name` varchar(100) DEFAULT NULL,
  `duration` varchar(50) DEFAULT NULL,
  `study_center` varchar(100) DEFAULT NULL,
  `module_id` int(11) DEFAULT NULL,
  `written_marks` int(11) DEFAULT NULL,
  `practical_marks` int(11) DEFAULT NULL,
  `project_marks` int(11) DEFAULT NULL,
  `viva_marks` int(11) DEFAULT NULL,
  `percentage` int(11) DEFAULT NULL,
  `grade` varchar(10) DEFAULT NULL,
  `exam_date` date DEFAULT NULL,
  `issue_date` date DEFAULT NULL,
  `approved` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `total_written_marks` int(11) DEFAULT 0,
  `total_practical_marks` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `certificates`
--

INSERT INTO `certificates` (`id`, `student_id`, `student_code`, `name`, `dob`, `father_name`, `mother_name`, `course_name`, `duration`, `study_center`, `module_id`, `written_marks`, `practical_marks`, `project_marks`, `viva_marks`, `percentage`, `grade`, `exam_date`, `issue_date`, `approved`, `created_at`, `total_written_marks`, `total_practical_marks`) VALUES
(1, 4, 'STU152', 'Deepak kumar', '1998-04-05', 'Sanjay Prasad', 'shabya kumari', 'ADCA Plus', '1 YEAR', '0', NULL, 288, 275, 270, 266, 92, 'A+', NULL, NULL, 1, '2026-02-10 10:21:11', 0, 0),
(2, 6, 'STU20260211031639996', 'SUDHIR KUMAR', '1983-01-01', 'SHASHI KANT RAY', 'SHILA DEVI', 'ADCA', '1 YEAR', '0', NULL, 257, 234, 140, 140, 86, 'A', NULL, NULL, 1, '2026-02-11 05:17:55', 0, 0),
(3, 7, 'STU20260221031414902', 'ANITA KUMARI', '1996-05-09', 'MAHESH RAY', 'MAHA DEVI', 'ADCA', '1 YEAR', '0', NULL, 277, 280, 128, 122, 90, 'A', NULL, NULL, 1, '2026-03-05 03:01:56', 0, 0),
(4, 16, 'STU20260313120115800', 'MUKESH KUMAR', '1998-02-15', 'MANCHIT MAHTO', 'SUNAINA DEVI', 'ADCA', '1 YEAR', '0', NULL, 282, 280, 137, 140, 93, 'A+', NULL, NULL, 1, '2026-03-18 03:29:28', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `course_code` varchar(10) NOT NULL,
  `course_name` varchar(255) NOT NULL,
  `duration` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `details` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `course_subjects`
--

CREATE TABLE `course_subjects` (
  `id` int(11) NOT NULL,
  `course_name` varchar(100) NOT NULL,
  `subject_name` varchar(100) NOT NULL,
  `max_marks` int(11) DEFAULT 100,
  `is_practical` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `districts`
--

CREATE TABLE `districts` (
  `id` int(11) NOT NULL,
  `state_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `districts`
--

INSERT INTO `districts` (`id`, `state_id`, `name`) VALUES
(1, 1, 'Anantapur'),
(2, 1, 'Chittoor'),
(3, 1, 'East Godavari'),
(4, 1, 'Guntur'),
(5, 1, 'Kadapa'),
(6, 1, 'Krishna'),
(7, 1, 'Kurnool'),
(8, 1, 'Nellore'),
(9, 1, 'Prakasam'),
(10, 1, 'Srikakulam'),
(11, 1, 'Visakhapatnam'),
(12, 1, 'Vizianagaram'),
(13, 1, 'West Godavari'),
(14, 2, 'Tawang'),
(15, 2, 'West Kameng'),
(16, 2, 'East Kameng'),
(17, 2, 'Papum Pare'),
(18, 2, 'Kurung Kumey'),
(19, 2, 'Kra Daadi'),
(20, 2, 'Lower Subansiri'),
(21, 2, 'Upper Subansiri'),
(22, 2, 'West Siang'),
(23, 2, 'East Siang'),
(24, 2, 'Siang'),
(25, 2, 'Upper Siang'),
(26, 2, 'Lower Siang'),
(27, 2, 'Lower Dibang Valley'),
(28, 2, 'Dibang Valley'),
(29, 2, 'Anjaw'),
(30, 2, 'Lohit'),
(31, 2, 'Namsai'),
(32, 2, 'Changlang'),
(33, 2, 'Tirap'),
(34, 2, 'Longding'),
(35, 3, 'Baksa'),
(36, 3, 'Barpeta'),
(37, 3, 'Biswanath'),
(38, 3, 'Bongaigaon'),
(39, 3, 'Cachar'),
(40, 3, 'Charaideo'),
(41, 3, 'Chirang'),
(42, 3, 'Darrang'),
(43, 3, 'Dhemaji'),
(44, 3, 'Dhubri'),
(45, 3, 'Dibrugarh'),
(46, 3, 'Goalpara'),
(47, 3, 'Golaghat'),
(48, 3, 'Hailakandi'),
(49, 3, 'Hojai'),
(50, 3, 'Jorhat'),
(51, 3, 'Kamrup'),
(52, 3, 'Kamrup Metropolitan'),
(53, 3, 'Karbi Anglong'),
(54, 3, 'Karimganj'),
(55, 3, 'Kokrajhar'),
(56, 3, 'Lakhimpur'),
(57, 3, 'Majuli'),
(58, 3, 'Morigaon'),
(59, 3, 'Nagaon'),
(60, 3, 'Nalbari'),
(61, 3, 'Sivasagar'),
(62, 3, 'Sonitpur'),
(63, 3, 'South Salmara-Mankachar'),
(64, 3, 'Tinsukia'),
(65, 3, 'Udalguri'),
(66, 3, 'West Karbi Anglong'),
(67, 4, 'Araria'),
(68, 4, 'Arwal'),
(69, 4, 'Aurangabad'),
(70, 4, 'Banka'),
(71, 4, 'Begusarai'),
(72, 4, 'Bhagalpur'),
(73, 4, 'Bhojpur'),
(74, 4, 'Buxar'),
(75, 4, 'Darbhanga'),
(76, 4, 'East Champaran'),
(77, 4, 'Gaya'),
(78, 4, 'Gopalganj'),
(79, 4, 'Jamui'),
(80, 4, 'Jehanabad'),
(81, 4, 'Kaimur'),
(82, 4, 'Katihar'),
(83, 4, 'Khagaria'),
(84, 4, 'Kishanganj'),
(85, 4, 'Lakhisarai'),
(86, 4, 'Madhepura'),
(87, 4, 'Madhubani'),
(88, 4, 'Munger'),
(89, 4, 'Muzaffarpur'),
(90, 4, 'Nalanda'),
(91, 4, 'Nawada'),
(92, 4, 'Patna'),
(93, 4, 'Purnia'),
(94, 4, 'Rohtas'),
(95, 4, 'Saharsa'),
(96, 4, 'Samastipur'),
(97, 4, 'Saran'),
(98, 4, 'Sheikhpura'),
(99, 4, 'Sheohar'),
(100, 4, 'Sitamarhi'),
(101, 4, 'Siwan'),
(102, 4, 'Supaul'),
(103, 4, 'Vaishali'),
(104, 4, 'West Champaran'),
(105, 5, 'Balod'),
(106, 5, 'Baloda Bazar'),
(107, 5, 'Balrampur'),
(108, 5, 'Bastar'),
(109, 5, 'Bemetara'),
(110, 5, 'Bijapur'),
(111, 5, 'Bilaspur'),
(112, 5, 'Dantewada'),
(113, 5, 'Dhamtari'),
(114, 5, 'Durg'),
(115, 5, 'Gariaband'),
(116, 5, 'Gaurella-Pendra-Marwahi'),
(117, 5, 'Janjgir-Champa'),
(118, 5, 'Jashpur'),
(119, 5, 'Kabirdham'),
(120, 5, 'Kanker'),
(121, 5, 'Kondagaon'),
(122, 5, 'Korba'),
(123, 5, 'Koriya'),
(124, 5, 'Mahasamund'),
(125, 5, 'Mungeli'),
(126, 5, 'Narayanpur'),
(127, 5, 'Raigarh'),
(128, 5, 'Raipur'),
(129, 5, 'Rajnandgaon'),
(130, 5, 'Sukma'),
(131, 5, 'Surajpur'),
(132, 5, 'Surguja'),
(133, 6, 'North Goa'),
(134, 6, 'South Goa'),
(135, 7, 'Ahmedabad'),
(136, 7, 'Amreli'),
(137, 7, 'Anand'),
(138, 7, 'Aravalli'),
(139, 7, 'Banaskantha'),
(140, 7, 'Bharuch'),
(141, 7, 'Bhavnagar'),
(142, 7, 'Botad'),
(143, 7, 'Chhota Udaipur'),
(144, 7, 'Dahod'),
(145, 7, 'Dang'),
(146, 7, 'Devbhoomi Dwarka'),
(147, 7, 'Gandhinagar'),
(148, 7, 'Gir Somnath'),
(149, 7, 'Jamnagar'),
(150, 7, 'Junagadh'),
(151, 7, 'Kheda'),
(152, 7, 'Kutch'),
(153, 7, 'Mahisagar'),
(154, 7, 'Mehsana'),
(155, 7, 'Morbi'),
(156, 7, 'Narmada'),
(157, 7, 'Navsari'),
(158, 7, 'Panchmahal'),
(159, 7, 'Patan'),
(160, 7, 'Porbandar'),
(161, 7, 'Rajkot'),
(162, 7, 'Sabarkantha'),
(163, 7, 'Surat'),
(164, 7, 'Surendranagar'),
(165, 7, 'Tapi'),
(166, 7, 'Vadodara'),
(167, 7, 'Valsad'),
(168, 8, 'Ambala'),
(169, 8, 'Bhiwani'),
(170, 8, 'Charkhi Dadri'),
(171, 8, 'Faridabad'),
(172, 8, 'Fatehabad'),
(173, 8, 'Gurugram'),
(174, 8, 'Hisar'),
(175, 8, 'Jhajjar'),
(176, 8, 'Jind'),
(177, 8, 'Kaithal'),
(178, 8, 'Karnal'),
(179, 8, 'Kurukshetra'),
(180, 8, 'Mahendragarh'),
(181, 8, 'Nuh'),
(182, 8, 'Palwal'),
(183, 8, 'Panchkula'),
(184, 8, 'Panipat'),
(185, 8, 'Rewari'),
(186, 8, 'Rohtak'),
(187, 8, 'Sirsa'),
(188, 8, 'Sonipat'),
(189, 8, 'Yamunanagar'),
(190, 9, 'Bilaspur'),
(191, 9, 'Chamba'),
(192, 9, 'Hamirpur'),
(193, 9, 'Kangra'),
(194, 9, 'Kinnaur'),
(195, 9, 'Kullu'),
(196, 9, 'Lahaul and Spiti'),
(197, 9, 'Mandi'),
(198, 9, 'Shimla'),
(199, 9, 'Sirmaur'),
(200, 9, 'Solan'),
(201, 9, 'Una'),
(202, 10, 'Bokaro'),
(203, 10, 'Chatra'),
(204, 10, 'Deoghar'),
(205, 10, 'Dhanbad'),
(206, 10, 'Dumka'),
(207, 10, 'East Singhbhum'),
(208, 10, 'Garhwa'),
(209, 10, 'Giridih'),
(210, 10, 'Godda'),
(211, 10, 'Gumla'),
(212, 10, 'Hazaribagh'),
(213, 10, 'Jamtara'),
(214, 10, 'Khunti'),
(215, 10, 'Koderma'),
(216, 10, 'Latehar'),
(217, 10, 'Lohardaga'),
(218, 10, 'Pakur'),
(219, 10, 'Palamu'),
(220, 10, 'Ramgarh'),
(221, 10, 'Ranchi'),
(222, 10, 'Sahebganj'),
(223, 10, 'Seraikela Kharsawan'),
(224, 10, 'Simdega'),
(225, 10, 'West Singhbhum'),
(226, 11, 'Bagalkot'),
(227, 11, 'Ballari'),
(228, 11, 'Belagavi'),
(229, 11, 'Bengaluru Rural'),
(230, 11, 'Bengaluru Urban'),
(231, 11, 'Bidar'),
(232, 11, 'Chamarajanagar'),
(233, 11, 'Chikballapur'),
(234, 11, 'Chikkamagaluru'),
(235, 11, 'Chitradurga'),
(236, 11, 'Dakshina Kannada'),
(237, 11, 'Davanagere'),
(238, 11, 'Dharwad'),
(239, 11, 'Gadag'),
(240, 11, 'Hassan'),
(241, 11, 'Haveri'),
(242, 11, 'Kalaburagi'),
(243, 11, 'Kodagu'),
(244, 11, 'Kolar'),
(245, 11, 'Koppal'),
(246, 11, 'Mandya'),
(247, 11, 'Mysuru'),
(248, 11, 'Raichur'),
(249, 11, 'Ramanagara'),
(250, 11, 'Shivamogga'),
(251, 11, 'Tumakuru'),
(252, 11, 'Udupi'),
(253, 11, 'Uttara Kannada'),
(254, 11, 'Vijayapura'),
(255, 11, 'Yadgir'),
(256, 12, 'Alappuzha'),
(257, 12, 'Ernakulam'),
(258, 12, 'Idukki'),
(259, 12, 'Kannur'),
(260, 12, 'Kasaragod'),
(261, 12, 'Kollam'),
(262, 12, 'Kottayam'),
(263, 12, 'Kozhikode'),
(264, 12, 'Malappuram'),
(265, 12, 'Palakkad'),
(266, 12, 'Pathanamthitta'),
(267, 12, 'Thiruvananthapuram'),
(268, 12, 'Thrissur'),
(269, 12, 'Wayanad'),
(270, 13, 'Agar Malwa'),
(271, 13, 'Alirajpur'),
(272, 13, 'Anuppur'),
(273, 13, 'Ashoknagar'),
(274, 13, 'Balaghat'),
(275, 13, 'Barwani'),
(276, 13, 'Betul'),
(277, 13, 'Bhind'),
(278, 13, 'Bhopal'),
(279, 13, 'Burhanpur'),
(280, 13, 'Chhatarpur'),
(281, 13, 'Chhindwara'),
(282, 13, 'Damoh'),
(283, 13, 'Datia'),
(284, 13, 'Dewas'),
(285, 13, 'Dhar'),
(286, 13, 'Dindori'),
(287, 13, 'Guna'),
(288, 13, 'Gwalior'),
(289, 13, 'Harda'),
(290, 13, 'Hoshangabad'),
(291, 13, 'Indore'),
(292, 13, 'Jabalpur'),
(293, 13, 'Jhabua'),
(294, 13, 'Katni'),
(295, 13, 'Khandwa'),
(296, 13, 'Khargone'),
(297, 13, 'Mandla'),
(298, 13, 'Mandsaur'),
(299, 13, 'Morena'),
(300, 13, 'Narsinghpur'),
(301, 13, 'Neemuch'),
(302, 13, 'Panna'),
(303, 13, 'Raisen'),
(304, 13, 'Rajgarh'),
(305, 13, 'Ratlam'),
(306, 13, 'Rewa'),
(307, 13, 'Sagar'),
(308, 13, 'Satna'),
(309, 13, 'Sehore'),
(310, 13, 'Seoni'),
(311, 13, 'Shahdol'),
(312, 13, 'Shajapur'),
(313, 13, 'Sheopur'),
(314, 13, 'Shivpuri'),
(315, 13, 'Sidhi'),
(316, 13, 'Singrauli'),
(317, 13, 'Tikamgarh'),
(318, 13, 'Ujjain'),
(319, 13, 'Umaria'),
(320, 13, 'Vidisha'),
(321, 14, 'Ahmednagar'),
(322, 14, 'Akola'),
(323, 14, 'Amravati'),
(324, 14, 'Aurangabad'),
(325, 14, 'Beed'),
(326, 14, 'Bhandara'),
(327, 14, 'Buldhana'),
(328, 14, 'Chandrapur'),
(329, 14, 'Dhule'),
(330, 14, 'Gadchiroli'),
(331, 14, 'Gondia'),
(332, 14, 'Hingoli'),
(333, 14, 'Jalgaon'),
(334, 14, 'Jalna'),
(335, 14, 'Kolhapur'),
(336, 14, 'Latur'),
(337, 14, 'Mumbai City'),
(338, 14, 'Mumbai Suburban'),
(339, 14, 'Nagpur'),
(340, 14, 'Nanded'),
(341, 14, 'Nandurbar'),
(342, 14, 'Nashik'),
(343, 14, 'Osmanabad'),
(344, 14, 'Palghar'),
(345, 14, 'Parbhani'),
(346, 14, 'Pune'),
(347, 14, 'Raigad'),
(348, 14, 'Ratnagiri'),
(349, 14, 'Sangli'),
(350, 14, 'Satara'),
(351, 14, 'Sindhudurg'),
(352, 14, 'Solapur'),
(353, 14, 'Thane'),
(354, 14, 'Wardha'),
(355, 14, 'Washim'),
(356, 14, 'Yavatmal'),
(357, 15, 'Bishnupur'),
(358, 15, 'Chandel'),
(359, 15, 'Churachandpur'),
(360, 15, 'Imphal East'),
(361, 15, 'Imphal West'),
(362, 15, 'Jiribam'),
(363, 15, 'Kakching'),
(364, 15, 'Kamjong'),
(365, 15, 'Kangpokpi'),
(366, 15, 'Noney'),
(367, 15, 'Pherzawl'),
(368, 15, 'Senapati'),
(369, 15, 'Tamenglong'),
(370, 15, 'Tengnoupal'),
(371, 15, 'Thoubal'),
(372, 15, 'Ukhrul'),
(373, 16, 'East Garo Hills'),
(374, 16, 'East Jaintia Hills'),
(375, 16, 'East Khasi Hills'),
(376, 16, 'North Garo Hills'),
(377, 16, 'Ri Bhoi'),
(378, 16, 'South Garo Hills'),
(379, 16, 'South West Garo Hills'),
(380, 16, 'South West Khasi Hills'),
(381, 16, 'West Garo Hills'),
(382, 16, 'West Jaintia Hills'),
(383, 16, 'West Khasi Hills'),
(384, 17, 'Aizawl'),
(385, 17, 'Champhai'),
(386, 17, 'Hnahthial'),
(387, 17, 'Khawzawl'),
(388, 17, 'Kolasib'),
(389, 17, 'Lawngtlai'),
(390, 17, 'Lunglei'),
(391, 17, 'Mamit'),
(392, 17, 'Saiha'),
(393, 17, 'Saitual'),
(394, 17, 'Serchhip'),
(395, 18, 'Chümoukedima'),
(396, 18, 'Dimapur'),
(397, 18, 'Kiphire'),
(398, 18, 'Kohima'),
(399, 18, 'Longleng'),
(400, 18, 'Mokokchung'),
(401, 18, 'Mon'),
(402, 18, 'Niuland'),
(403, 18, 'Peren'),
(404, 18, 'Phek'),
(405, 18, 'Shamator'),
(406, 18, 'Tseminyü'),
(407, 18, 'Tuensang'),
(408, 18, 'Wokha'),
(409, 18, 'Zünheboto'),
(410, 19, 'Angul'),
(411, 19, 'Balangir'),
(412, 19, 'Balasore'),
(413, 19, 'Bargarh'),
(414, 19, 'Bhadrak'),
(415, 19, 'Boudh'),
(416, 19, 'Cuttack'),
(417, 19, 'Deogarh'),
(418, 19, 'Dhenkanal'),
(419, 19, 'Gajapati'),
(420, 19, 'Ganjam'),
(421, 19, 'Jagatsinghpur'),
(422, 19, 'Jajpur'),
(423, 19, 'Jharsuguda'),
(424, 19, 'Kalahandi'),
(425, 19, 'Kandhamal'),
(426, 19, 'Kendrapara'),
(427, 19, 'Kendujhar'),
(428, 19, 'Khordha'),
(429, 19, 'Koraput'),
(430, 19, 'Malkangiri'),
(431, 19, 'Mayurbhanj'),
(432, 19, 'Nabarangpur'),
(433, 19, 'Nayagarh'),
(434, 19, 'Nuapada'),
(435, 19, 'Puri'),
(436, 19, 'Rayagada'),
(437, 19, 'Sambalpur'),
(438, 19, 'Subarnapur'),
(439, 19, 'Sundargarh'),
(440, 20, 'Amritsar'),
(441, 20, 'Barnala'),
(442, 20, 'Bathinda'),
(443, 20, 'Faridkot'),
(444, 20, 'Fatehgarh Sahib'),
(445, 20, 'Fazilka'),
(446, 20, 'Ferozepur'),
(447, 20, 'Gurdaspur'),
(448, 20, 'Hoshiarpur'),
(449, 20, 'Jalandhar'),
(450, 20, 'Kapurthala'),
(451, 20, 'Ludhiana'),
(452, 20, 'Malerkotla'),
(453, 20, 'Mansa'),
(454, 20, 'Moga'),
(455, 20, 'Muktsar'),
(456, 20, 'Pathankot'),
(457, 20, 'Patiala'),
(458, 20, 'Rupnagar'),
(459, 20, 'Sahibzada Ajit Singh Nagar'),
(460, 20, 'Sangrur'),
(461, 20, 'Shaheed Bhagat Singh Nagar'),
(462, 20, 'Tarn Taran'),
(463, 21, 'Ajmer'),
(464, 21, 'Alwar'),
(465, 21, 'Banswara'),
(466, 21, 'Baran'),
(467, 21, 'Barmer'),
(468, 21, 'Bharatpur'),
(469, 21, 'Bhilwara'),
(470, 21, 'Bikaner'),
(471, 21, 'Bundi'),
(472, 21, 'Chittorgarh'),
(473, 21, 'Churu'),
(474, 21, 'Dausa'),
(475, 21, 'Dholpur'),
(476, 21, 'Dungarpur'),
(477, 21, 'Ganganagar'),
(478, 21, 'Hanumangarh'),
(479, 21, 'Jaipur'),
(480, 21, 'Jaisalmer'),
(481, 21, 'Jalore'),
(482, 21, 'Jhalawar'),
(483, 21, 'Jhunjhunu'),
(484, 21, 'Jodhpur'),
(485, 21, 'Karauli'),
(486, 21, 'Kota'),
(487, 21, 'Nagaur'),
(488, 21, 'Pali'),
(489, 21, 'Pratapgarh'),
(490, 21, 'Rajsamand'),
(491, 21, 'Sawai Madhopur'),
(492, 21, 'Sikar'),
(493, 21, 'Sirohi'),
(494, 21, 'Tonk'),
(495, 21, 'Udaipur'),
(496, 22, 'Gangtok'),
(497, 22, 'Gyalshing'),
(498, 22, 'Mangan'),
(499, 22, 'Namchi'),
(500, 22, 'Pakyong'),
(501, 22, 'Soreng'),
(502, 23, 'Ariyalur'),
(503, 23, 'Chengalpattu'),
(504, 23, 'Chennai'),
(505, 23, 'Coimbatore'),
(506, 23, 'Cuddalore'),
(507, 23, 'Dharmapuri'),
(508, 23, 'Dindigul'),
(509, 23, 'Erode'),
(510, 23, 'Kallakurichi'),
(511, 23, 'Kanchipuram'),
(512, 23, 'Kanyakumari'),
(513, 23, 'Karur'),
(514, 23, 'Krishnagiri'),
(515, 23, 'Madurai'),
(516, 23, 'Nagapattinam'),
(517, 23, 'Namakkal'),
(518, 23, 'Nilgiris'),
(519, 23, 'Perambalur'),
(520, 23, 'Pudukkottai'),
(521, 23, 'Ramanathapuram'),
(522, 23, 'Ranipet'),
(523, 23, 'Salem'),
(524, 23, 'Sivaganga'),
(525, 23, 'Tenkasi'),
(526, 23, 'Thanjavur'),
(527, 23, 'Theni'),
(528, 23, 'Thoothukudi'),
(529, 23, 'Tiruchirappalli'),
(530, 23, 'Tirunelveli'),
(531, 23, 'Tirupathur'),
(532, 23, 'Tiruppur'),
(533, 23, 'Tiruvallur'),
(534, 23, 'Tiruvannamalai'),
(535, 23, 'Tiruvarur'),
(536, 23, 'Vellore'),
(537, 23, 'Viluppuram'),
(538, 23, 'Virudhunagar'),
(539, 24, 'Adilabad'),
(540, 24, 'Bhadradri Kothagudem'),
(541, 24, 'Hanamkonda'),
(542, 24, 'Hyderabad'),
(543, 24, 'Jagtial'),
(544, 24, 'Jangaon'),
(545, 24, 'Jayashankar Bhupalpally'),
(546, 24, 'Jogulamba Gadwal'),
(547, 24, 'Kamareddy'),
(548, 24, 'Karimnagar'),
(549, 24, 'Khammam'),
(550, 24, 'Kumuram Bheem'),
(551, 24, 'Mahabubabad'),
(552, 24, 'Mahabubnagar'),
(553, 24, 'Mancherial'),
(554, 24, 'Medak'),
(555, 24, 'Medchal–Malkajgiri'),
(556, 24, 'Mulugu'),
(557, 24, 'Nagarkurnool'),
(558, 24, 'Nalgonda'),
(559, 24, 'Narayanpet'),
(560, 24, 'Nirmal'),
(561, 24, 'Nizamabad'),
(562, 24, 'Peddapalli'),
(563, 24, 'Rajanna Sircilla'),
(564, 24, 'Ranga Reddy'),
(565, 24, 'Sangareddy'),
(566, 24, 'Siddipet'),
(567, 24, 'Suryapet'),
(568, 24, 'Vikarabad'),
(569, 24, 'Wanaparthy'),
(570, 24, 'Warangal'),
(571, 24, 'Yadadri Bhuvanagiri'),
(572, 25, 'Dhalai'),
(573, 25, 'Gomati'),
(574, 25, 'Khowai'),
(575, 25, 'North Tripura'),
(576, 25, 'Sepahijala'),
(577, 25, 'South Tripura'),
(578, 25, 'Unakoti'),
(579, 25, 'West Tripura'),
(580, 26, 'Agra'),
(581, 26, 'Aligarh'),
(582, 26, 'Allahabad'),
(583, 26, 'Ambedkar Nagar'),
(584, 26, 'Amethi'),
(585, 26, 'Amroha'),
(586, 26, 'Auraiya'),
(587, 26, 'Azamgarh'),
(588, 26, 'Baghpat'),
(589, 26, 'Bahraich'),
(590, 26, 'Ballia'),
(591, 26, 'Balrampur'),
(592, 26, 'Banda'),
(593, 26, 'Barabanki'),
(594, 26, 'Bareilly'),
(595, 26, 'Basti'),
(596, 26, 'Bhadohi'),
(597, 26, 'Bijnor'),
(598, 26, 'Budaun'),
(599, 26, 'Bulandshahr'),
(600, 26, 'Chandauli'),
(601, 26, 'Chitrakoot'),
(602, 26, 'Deoria'),
(603, 26, 'Etah'),
(604, 26, 'Etawah'),
(605, 26, 'Faizabad'),
(606, 26, 'Farrukhabad'),
(607, 26, 'Fatehpur'),
(608, 26, 'Firozabad'),
(609, 26, 'Gautam Buddh Nagar'),
(610, 26, 'Ghaziabad'),
(611, 26, 'Ghazipur'),
(612, 26, 'Gonda'),
(613, 26, 'Gorakhpur'),
(614, 26, 'Hamirpur'),
(615, 26, 'Hapur'),
(616, 26, 'Hardoi'),
(617, 26, 'Hathras'),
(618, 26, 'Jalaun'),
(619, 26, 'Jaunpur'),
(620, 26, 'Jhansi'),
(621, 26, 'Kannauj'),
(622, 26, 'Kanpur Dehat'),
(623, 26, 'Kanpur Nagar'),
(624, 26, 'Kasganj'),
(625, 26, 'Kaushambi'),
(626, 26, 'Kheri'),
(627, 26, 'Kushinagar'),
(628, 26, 'Lalitpur'),
(629, 26, 'Lucknow'),
(630, 26, 'Maharajganj'),
(631, 26, 'Mahoba'),
(632, 26, 'Mainpuri'),
(633, 26, 'Mathura'),
(634, 26, 'Mau'),
(635, 26, 'Meerut'),
(636, 26, 'Mirzapur'),
(637, 26, 'Moradabad'),
(638, 26, 'Muzaffarnagar'),
(639, 26, 'Pilibhit'),
(640, 26, 'Pratapgarh'),
(641, 26, 'Prayagraj'),
(642, 26, 'Raebareli'),
(643, 26, 'Rampur'),
(644, 26, 'Saharanpur'),
(645, 26, 'Sambhal'),
(646, 26, 'Sant Kabir Nagar'),
(647, 26, 'Shahjahanpur'),
(648, 26, 'Shamli'),
(649, 26, 'Shravasti'),
(650, 26, 'Siddharthnagar'),
(651, 26, 'Sitapur'),
(652, 26, 'Sonbhadra'),
(653, 26, 'Sultanpur'),
(654, 26, 'Unnao'),
(655, 26, 'Varanasi'),
(656, 27, 'Almora'),
(657, 27, 'Bageshwar'),
(658, 27, 'Chamoli'),
(659, 27, 'Champawat'),
(660, 27, 'Dehradun'),
(661, 27, 'Haridwar'),
(662, 27, 'Nainital'),
(663, 27, 'Pauri Garhwal'),
(664, 27, 'Pithoragarh'),
(665, 27, 'Rudraprayag'),
(666, 27, 'Tehri Garhwal'),
(667, 27, 'Udham Singh Nagar'),
(668, 27, 'Uttarkashi'),
(669, 28, 'Alipurduar'),
(670, 28, 'Bankura'),
(671, 28, 'Birbhum'),
(672, 28, 'Cooch Behar'),
(673, 28, 'Dakshin Dinajpur'),
(674, 28, 'Darjeeling'),
(675, 28, 'Hooghly'),
(676, 28, 'Howrah'),
(677, 28, 'Jalpaiguri'),
(678, 28, 'Jhargram'),
(679, 28, 'Kalimpong'),
(680, 28, 'Kolkata'),
(681, 28, 'Malda'),
(682, 28, 'Murshidabad'),
(683, 28, 'Nadia'),
(684, 28, 'North 24 Parganas'),
(685, 28, 'Paschim Bardhaman'),
(686, 28, 'Paschim Medinipur'),
(687, 28, 'Purba Bardhaman'),
(688, 28, 'Purba Medinipur'),
(689, 28, 'Purulia'),
(690, 28, 'South 24 Parganas'),
(691, 28, 'Uttar Dinajpur'),
(692, 29, 'Nicobar'),
(693, 29, 'North and Middle Andaman'),
(694, 29, 'South Andaman'),
(695, 30, 'Chandigarh'),
(696, 31, 'Dadra and Nagar Haveli'),
(697, 31, 'Daman'),
(698, 31, 'Diu'),
(699, 32, 'Central Delhi'),
(700, 32, 'East Delhi'),
(701, 32, 'New Delhi'),
(702, 32, 'North Delhi'),
(703, 32, 'North East Delhi'),
(704, 32, 'North West Delhi'),
(705, 32, 'Shahdara'),
(706, 32, 'South Delhi'),
(707, 32, 'South East Delhi'),
(708, 32, 'South West Delhi'),
(709, 32, 'West Delhi'),
(710, 33, 'Anantnag'),
(711, 33, 'Bandipora'),
(712, 33, 'Baramulla'),
(713, 33, 'Budgam'),
(714, 33, 'Doda'),
(715, 33, 'Ganderbal'),
(716, 33, 'Jammu'),
(717, 33, 'Kathua'),
(718, 33, 'Kishtwar'),
(719, 33, 'Kulgam'),
(720, 33, 'Kupwara'),
(721, 33, 'Poonch'),
(722, 33, 'Pulwama'),
(723, 33, 'Rajouri'),
(724, 33, 'Ramban'),
(725, 33, 'Reasi'),
(726, 33, 'Samba'),
(727, 33, 'Shopian'),
(728, 33, 'Srinagar'),
(729, 33, 'Udhampur'),
(730, 34, 'Kargil'),
(731, 34, 'Leh'),
(732, 35, 'Lakshadweep'),
(733, 36, 'Karaikal'),
(734, 36, 'Mahe'),
(735, 36, 'Puducherry'),
(736, 36, 'Yanam');

-- --------------------------------------------------------

--
-- Table structure for table `exam_schedule`
--

CREATE TABLE `exam_schedule` (
  `id` int(11) NOT NULL,
  `exam_name` varchar(255) NOT NULL,
  `center_id` int(11) DEFAULT NULL,
  `course_id` int(11) DEFAULT NULL,
  `module_id` int(11) DEFAULT NULL,
  `exam_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `duration_minutes` int(11) DEFAULT NULL,
  `total_marks` int(11) DEFAULT 100,
  `passing_marks` int(11) DEFAULT 35,
  `instructions` text DEFAULT NULL,
  `status` enum('Scheduled','Completed','Cancelled') DEFAULT 'Scheduled',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `members`
--

CREATE TABLE `members` (
  `id` int(11) NOT NULL,
  `member_type` varchar(50) DEFAULT NULL,
  `center_name` varchar(100) DEFAULT NULL,
  `owner_name` varchar(100) DEFAULT NULL,
  `father_name` varchar(255) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `blood_group` varchar(255) DEFAULT NULL,
  `p_address` varchar(255) DEFAULT NULL,
  `block` varchar(255) DEFAULT NULL,
  `p_district` varchar(255) DEFAULT NULL,
  `p_state` varchar(255) DEFAULT NULL,
  `created_at` date DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `district` varchar(100) DEFAULT NULL,
  `post_office` varchar(255) DEFAULT NULL,
  `pincode` varchar(10) DEFAULT NULL,
  `id_card` varchar(255) DEFAULT NULL,
  `reg_amount` decimal(10,2) DEFAULT NULL,
  `payment_mode` varchar(50) DEFAULT NULL,
  `payment_status` varchar(50) DEFAULT NULL,
  `member_status` varchar(20) NOT NULL DEFAULT 'Inactive',
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `unicode` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `member_information`
--

CREATE TABLE `member_information` (
  `id` int(11) NOT NULL,
  `member_type_id` int(11) DEFAULT NULL,
  `unicode` varchar(100) DEFAULT NULL,
  `applicant_name` varchar(100) NOT NULL,
  `father_name` varchar(100) NOT NULL,
  `mother_name` varchar(100) DEFAULT NULL,
  `mobile_number` varchar(15) NOT NULL,
  `email_id` varchar(100) NOT NULL,
  `uid_no` varchar(50) DEFAULT NULL,
  `pan_no` varchar(20) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `post_applied` varchar(100) DEFAULT NULL,
  `full_address` text DEFAULT NULL,
  `at_village` varchar(100) DEFAULT NULL,
  `via` varchar(100) DEFAULT NULL,
  `block` varchar(100) DEFAULT NULL,
  `police_station` varchar(100) DEFAULT NULL,
  `district` varchar(100) DEFAULT NULL,
  `pincode` varchar(10) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `educational_qualification` varchar(255) DEFAULT NULL,
  `extra_qualification` text DEFAULT NULL,
  `experience` text DEFAULT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `branch_name` varchar(100) DEFAULT NULL,
  `ifsc_code` varchar(20) DEFAULT NULL,
  `account_number` varchar(50) DEFAULT NULL,
  `account_type` varchar(20) DEFAULT NULL,
  `passport_photo` varchar(255) DEFAULT NULL,
  `reg_amount` decimal(10,2) DEFAULT 3500.00,
  `payment_mode` varchar(50) DEFAULT NULL,
  `payment_status` varchar(20) DEFAULT 'Pending',
  `member_status` varchar(20) DEFAULT 'Inactive',
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `member_information`
--

INSERT INTO `member_information` (`id`, `member_type_id`, `unicode`, `applicant_name`, `father_name`, `mother_name`, `mobile_number`, `email_id`, `uid_no`, `pan_no`, `date_of_birth`, `post_applied`, `full_address`, `at_village`, `via`, `block`, `police_station`, `district`, `pincode`, `state`, `educational_qualification`, `extra_qualification`, `experience`, `bank_name`, `branch_name`, `ifsc_code`, `account_number`, `account_type`, `passport_photo`, `reg_amount`, `payment_mode`, `payment_status`, `member_status`, `username`, `password`, `created_at`) VALUES
(2, 4, 'TEMP_1770205685_2013', 'lisha thakur', 'Deepak kumar', 'Manisha kumari', '7061036823', 'lisha@gmail.com', '879348234567', 'LZIKP2334F', '1994-07-04', 'Block Coordinator', 'khorampur\r\nkhorampur', 'KHORAMPUR', 'ISLAMPUR', 'islampur', 'ISLAMPUR', 'NALNDA', '801303', 'Bihar', 'Post Graduate', 'COMPUTER  SCIENCE', '7 year', 'SBI', 'MEHANDIGANJ', 'MEHSBI007', '347890003454', 'Salary', 'uploads/members/1770362538_698596aa05be6.png', 3500.00, 'Online', 'Paid', 'Active', 'LISHA@123', '$2y$10$T9EAYxE83TCjp21Ul.z1teHV8lAbeF6f0DVBY4bQpHpyejuqdm2du', '2026-02-04 11:48:05'),
(4, 2, NULL, 'manisha kumari', 'srwan thakur', 'jyoti devi', '8734563456', 'manisha@gmail.com', '873423453445', 'LZIKP2334F', '2002-08-14', 'Zonal Manager', 'khorampur\r\nkhorampur', 'KHORAMPUR', 'ISLAMPUR', 'islampur', 'ISLAMPUR', 'NALNDA', '801303', 'Bihar', 'Post Graduate', 'COMPUTER  SCIENCE', '4 YER', 'Yes', 'MEHANDIGANJ', 'sbn100gh', '234500893456', 'Savings', '0', 3500.00, 'Cash', 'Paid', 'Active', 'manideep', '$2y$10$JY.MUX0Q4i0YWiQ5TVDGxuyJrnYuB0XJzXOpm8XqvQRMDUVq8bGqy', '2026-02-04 11:58:07'),
(7, 4, 'SIET/BC/BI/0004/2026', 'KUMAR SUDHANSHU RANJAN', 'SURESH RAY', 'MAKIYA DEVI', '9262222201', 'kumarsudhanshuranjan25@gmail.com', '', '', '1991-12-31', 'Block Coordinator', 'AT-PATPARA SOUTH PO-PATPARA NORTH PS-BIBHUTIPUR', 'PATPARA SOUTH', 'SINGHIAGHAT', 'BIBHUTIPUR', 'BIBHUTIPUR', 'SAMASTIPUR', '848236', 'Bihar', 'Graduate', '', '', 'SBI', 'AMY MATHURAPUR', 'SBIN0006388', '41470211421', 'Savings', 'uploads/members/1771402585_69957559dc339.jpeg', 3500.00, 'Cash', 'Paid', 'Active', 'Sudhanshu01', '$2y$10$r7dvxx5ZpQonFm85dIGYfeMr70I93O0WbjWYHj.4EqdhhQ0aUyPmq', '2026-02-18 08:16:25'),
(8, 2, 'SIET/ZM/BI/0004/2026', 'PRASHANT KUMAR THAKUR', 'LATE SHREEKANT THAKUR', 'GITA DEVI', '9931245019', 'prashantkumar0221982@gmail.com', '', '', '1982-02-02', 'Zonal Manager', 'At+Po-Bazidpur Meyari', 'At+Po- Bazidpur Meyari', '', 'sarairanjan', '', 'SAMASTIPUR', '848114', 'Bihar', 'Post Graduate', '', '', 'CENTERAL BANK OF INDIA', 'SARAIRANJAN', 'CBION0005862', '2523545225355', 'Savings', 'uploads/members/1771748401_699abc319840d.jpeg', 3500.00, 'Cash', 'Pending', 'Active', '1234567890', '$2y$10$qjwz7bWkgyb4a0IYuTRpLerGzV5GrpbeW4K89uOF7zZospzb/QfZu', '2026-02-22 08:20:01'),
(9, 3, 'SIET/DC/BI/0005/2026', 'VIKRANT KUMAR', 'SHRIKANT THAKUR', 'GITA DEVI', '9798968765', 'kumarvikrant830@gmail.com', '', '', '1986-08-07', 'District Coordinator', 'At+Po-Bazidpur Meyari', 'At+Po- Bazidpur Meyari', '', 'sarairanjan', '', 'samastipur', '848114', 'Bihar', 'Post Graduate', '', '', 'panjab national Bank', 'samastipur', 'punb05456655', '52645562555', 'Savings', 'uploads/members/1771749002_699abe8ab1aa4.jpeg', 3500.00, 'Cash', 'Pending', 'Active', 'Vik123456789', '$2y$10$1CaO0gYSWi8A4oOfRcaRL./Zm0/nXiplqf6M.BHpGjOLeLV73XKRe', '2026-02-22 08:30:02'),
(10, 1, 'SIET/SN/BI/0006/2026', 'MUKESH KUMAR', 'CHALITAR DAS', 'SITA DEVI', '7319809300', 'mukeshkumar221129@gmail.com', '', '', '1983-01-05', 'State Nodal', 'at+po-Satanpur', 'At-wazidpur, Po-Satanpur', '', 'Ujiyarpur', '', 'samastipur', '848132', 'Bihar', 'Post Graduate', '', '', 'SBI', 'UJIYARPUR', 'SBIN0004659', '52645562555', 'Savings', 'uploads/members/1771752624_699accb06eb10.jpeg', 3500.00, 'Cash', 'Pending', 'Active', 'Mukesh@123', '$2y$10$jyeOK6K8bU9qIMTQtDQF/OPMgo2p0UnKvUUYBm9KGE47UgpBgj3Wq', '2026-02-22 09:30:24'),
(11, 1, 'SIET/SN/BI/0007/2026', 'SANJEET KUMAR', 'RAM CHANDRA RAY', 'RAM JYOTI DEVI', '6200591095', 'sanjeetcomputer1@gmail.com', '', '', '1985-02-12', 'State Nodal', 'at+po-Satanpur', 'At+Po-Satanpur', '', 'UJIYARPUR', '', 'samastipur', '848132', 'Bihar', 'Graduate', '', '', 'SBI', 'UJIYARPUR', 'SBIN0004659', '38906381349', 'Savings', 'uploads/members/1771753440_699acfe001b7d.jpeg', 3500.00, 'Cash', 'Paid', 'Active', 'sanjeetkumar122853@gmail.com', '$2y$10$RnUO32HBHsBeScqCn3S2A.JvqBnOd3HzWC8oE2dxPVaeoneJx9x/K', '2026-02-22 09:44:00'),
(12, 3, 'SIET/DC/BI/0008/2026', 'DHARMENDRA KUMAR PASWAN', 'BALESHWAR PASWAN', 'BACHCHI DEVI', '7033597470', 'dhamendra.kumar@gmail.com', '', '', '1993-06-07', 'District Coordinator', 'At+Po-Unsar', 'At+Po-Unsar', '', 'Bochan', '', 'MUZAFFAR PUR', '843103', 'Bihar', 'Graduate', '', '', 'Union Bank of India', 'Bajitpur Majhauli', 'UBIN0546160', '461602010360119', 'Savings', 'uploads/members/1771758778_699ae4ba2faae.jpeg', 3500.00, 'Cash', 'Pending', 'Active', 'aboutdharma176@gmail.com', '$2y$10$jDHtBEgfdWo9hMOeTbmRA.x13Lkvfixr4VKL6GdKiFU6bpXX3.OAe', '2026-02-22 11:12:58'),
(13, 2, 'SIET/ZM/BI/0009/2026', 'SATYAM KUMAR', 'SANI KUMAR MAHTO', 'REKHA DEVI', '7324026794', 'satysmmbos0101@gmail.com', '', '', '2000-09-22', 'Zonal Manager', 'At+Po-Parpria', 'At+Po-Parpria', 'Dalsinghsarai', 'Ujiyarpur', 'ujiyarpur', 'Samastipur', '848114', 'Bihar', 'Graduate', '', '', 'SBI', 'AMY MATHURAPUR', 'SBIN0006388', '38906381349', 'Savings', 'uploads/members/1771759289_699ae6b98f6fa.jpeg', 3500.00, 'Cash', 'Pending', 'Active', 'satysmmbos0101@gmail.com', '$2y$10$x2h5GxGyb4eDSyFD6i0AbOUDKMr76POC6ifSGWxScprVHzgxavOvy', '2026-02-22 11:21:29'),
(14, 2, 'SIET/ZM/BI/0010/2026', 'MUKESH KUMAR', 'CHALITAR DAS', '', '9709714423', 'mukesh.5183@gmail.com', '', '', '1983-01-05', 'Zonal Manager', 'At-wazidpur, po-satanpur', 'At-wazidpur, po-satanpur', '', 'Ujiyarpur', '', 'SAMASTIPUR', '848134', 'Bihar', 'Post Graduate', '', '', 'DBGB', 'satanpur', 'PUNBMBGB06', '395422252224041', 'Savings', 'uploads/members/1771933415_699d8ee7944be.jpeg', 3500.00, 'Cash', 'Pending', 'Active', 'Mukesh@gmail.com', '$2y$10$ay9WJY81aB/EqXcDwK34eumRHiYltq7YL4esVpnBFoiPxEd7b/0zm', '2026-02-24 11:43:35'),
(15, 2, 'SIET/ZM/BI/0011/2026', 'JAY KUMAR', 'KRISLAY KUMAR', '', '9771523525', '89jaych13@gmail.com', '', '', '1989-10-03', 'Zonal Manager', 'AT+PO-MARWA GOPALPUR', 'AT+PO-MARWA GOPALPUR', '', 'VIDYAPATIAGAR', '', 'SAMASTIPUR', '848503', 'Bihar', 'Post Graduate', '', '', 'BANDHAN BANK', 'DALSINGH SARAI', 'BDBL0001431', '20200137301915', 'Savings', 'uploads/members/1772012449_699ec3a1057f2.jpeg', 3500.00, 'Cash', 'Pending', 'Active', 'Jay@12345', '$2y$10$.VJPLqyPnp9dsYUplcfGbeedZuKdX6YKRZWyid5RRuzE0xsgyMV.C', '2026-02-25 09:40:49'),
(16, 4, 'SIET/BC/BI/0012/2026', 'VINOD SAHANI', 'JAY RAM SAHANI', '', '9006009518', 'vinod.kumar@gmail.com', '', '', '1989-06-08', 'Block Coordinator', 'At-Mahisaur', 'Mahisaur', 'Jandaha', 'vaishali', 'Vaishali', 'vaishali', '844128', 'Bihar', 'Graduate', '', '', 'SBI', 'vaishali', 'SBIN0006388', '38906381349', 'Savings', 'uploads/members/1773140980_69affbf415b63.jpg', 3500.00, 'Cash', 'Paid', 'Active', 'Vinod@123', '$2y$10$Dzvy9konFWgvMIUvXshkg.8iMnei9mR2ToeZG8qzbWLe6LKGVRcs2', '2026-03-10 11:09:40'),
(17, 2, 'SIET/ZM/WE/0013/2026', 'MANJAY PRAKASH', 'RAM PRAKASH SINGH', 'YASHODA DEVI', '8102694200', 'manjayprakash0@gmail.com', '', '', '1997-07-24', 'Zonal Manager', 'House No-303B, Ward No-16 Bhagwanpur', 'House No-303B, Ward No-16 Bhagwanpur', '', 'kharagpur', 'kharagpur', 'west Medanipur', '721301', 'West Bengal', 'Post Graduate', 'Computer and ITI Electrician.', '', 'UCO Bank', 'Kharida bazar, Kharagpur', 'UCBA0002174', '21740110040676', 'Savings', 'uploads/members/1773666081_69b7ff218d08b.jpeg', 3500.00, 'Cash', 'Pending', 'Active', 'Prakash@123', '$2y$10$Sub36rF/bHk8h/1R6iTVbu2aGmD2Mu4ImyMDJV5srrMCqFoEJqGLy', '2026-03-16 12:32:11'),
(18, 3, 'SIET/DC/BI/0014/2026', 'RAHUL KUMAR MISHRA', 'SHRI RAMANAND MISHRA', 'LATE SHUSHILA DEVI', '9798009200', 'misharakumarhul1@gmail.com', '', '', '1989-07-11', 'District Coordinator', 'At+Po-Paroria', 'AT+PO-PARORIA', '', 'UJIYARPUR', '', 'SAMASTIPUR', '848114', 'Bihar', 'Graduate', '', '', 'SBI', 'DALSINGH SARAI', 'SBIN0006388', '395422252224041', 'Savings', 'uploads/members/1774183702_69bfe5162ae7b.jpeg', 3500.00, 'Cash', 'Pending', 'Active', 'Rahul@123', '$2y$10$y4A2p1BUpDz07xwVdXM1l.WXrGkQYMYNVOoOW/DFlvnN87y9o1iUy', '2026-03-22 12:48:22'),
(19, 1, 'SIET/SN/BI/0015/2026', 'Manhoan jii', 'Sanjay prsad', 'Katrina kaif', '7526972793', 'deepakkumar933041@gmail.com', '898979820845', 'LZIKP2334F', '1998-02-11', 'State Nodal', 'Lal shiv mandir\r\nPaijawa', 'KHORAMPUR', 'ISLAMPUR', 'patna shadar', 'ISLAMPUR', 'patna', '800008', 'Bihar', 'Post Graduate', 'COMPUTER  SCIENCE', '10 yr exprince from byjus', 'boi', 'patna bajarsmati', 'BKID0004400', '347890003454', 'Savings', 'uploads/members/1779099877_6a0ae8e543aeb.jpg', 3500.00, 'Cash', 'Pending', 'Active', 'manhon@123', '$2y$10$m2Rp2vBlooWadUsFk/NyFOg09VQqRnQfjFo5skJMClPhZuo.wIc9W', '2026-05-18 10:24:38');

-- --------------------------------------------------------

--
-- Table structure for table `member_types`
--

CREATE TABLE `member_types` (
  `id` int(11) NOT NULL,
  `type_name` varchar(50) NOT NULL,
  `level` int(11) DEFAULT 1,
  `parent_type_id` int(11) DEFAULT NULL,
  `code_prefix` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `member_types`
--

INSERT INTO `member_types` (`id`, `type_name`, `level`, `parent_type_id`, `code_prefix`) VALUES
(1, 'State Nodel', 1, NULL, 'SN'),
(2, 'Zonal Manager', 2, NULL, 'ZM'),
(3, 'District Coordinator', 3, NULL, 'DC'),
(4, 'Block Coordinator', 4, NULL, 'BC'),
(5, 'General Member', 5, NULL, 'GEN');

-- --------------------------------------------------------

--
-- Table structure for table `member_working_area`
--

CREATE TABLE `member_working_area` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `state_id` int(11) DEFAULT NULL,
  `district_ids` text DEFAULT NULL,
  `block_ids` text DEFAULT NULL,
  `assigned_date` date DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `member_working_area`
--

INSERT INTO `member_working_area` (`id`, `member_id`, `state_id`, `district_ids`, `block_ids`, `assigned_date`, `status`) VALUES
(1, 1, 4, '', '', '2026-02-02', 'Active'),
(2, 2, 4, '90', '1', '2026-02-04', 'Active'),
(3, 4, 4, '70,71,72,77', '', '2026-02-04', 'Active'),
(4, 5, 4, '88', '', '2026-02-04', 'Active'),
(0, 0, 4, '', '', '2026-02-05', 'Active'),
(0, 0, 4, '89', '', '2026-02-09', 'Active'),
(0, 0, 4, '89', '', '2026-02-10', 'Active'),
(0, 0, 4, '72,76,77,89,97', '', '2026-02-11', 'Active'),
(0, 0, 4, '89', '', '2026-02-11', 'Active'),
(0, 0, 4, '68,69,70,71,73', '', '2026-02-15', 'Active'),
(0, 0, 4, '92', '176', '2026-02-17', 'Active'),
(0, 0, 4, '92', '173', '2026-02-17', 'Active'),
(0, 6, 4, '', '', '2026-02-17', 'Active'),
(0, 7, 4, '96', '2', '2026-02-18', 'Active'),
(0, 8, 4, '71,84,93,96,103', '', '2026-02-22', 'Active'),
(0, 9, 4, '103', '', '2026-02-22', 'Active'),
(0, 10, 28, '', '', '2026-02-22', 'Active'),
(0, 11, 4, '', '', '2026-02-22', 'Active'),
(0, 12, 4, '89', '', '2026-02-22', 'Active'),
(0, 13, 4, '72,76,77,89,97', '', '2026-02-22', 'Active'),
(0, 14, 4, '67,75,83,87,95', '', '2026-02-24', 'Active'),
(0, 15, 4, '67,86,88,95,102', '', '2026-02-25', 'Active'),
(0, 16, 4, '103', '71', '2026-03-10', 'Active'),
(0, 17, 28, '670,676,680,686,688', '', '2026-03-16', 'Active'),
(0, 18, 4, '72', '', '2026-03-22', 'Active'),
(0, 19, 1, '', '', '2026-05-18', 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `modules`
--

CREATE TABLE `modules` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `module_name` varchar(255) NOT NULL,
  `max_written_marks` int(11) DEFAULT 100,
  `max_practical_marks` int(11) DEFAULT 100,
  `is_active` tinyint(4) DEFAULT 1,
  `module_code` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `duration_hours` int(11) DEFAULT NULL,
  `order_number` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `notification_type` enum('Exam_Scheduled','Question_Paper','General') DEFAULT 'General',
  `title` varchar(255) NOT NULL,
  `message` text DEFAULT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `receiver_type` enum('Center','Admin','All') DEFAULT 'All',
  `receiver_id` int(11) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `related_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `onlinestudents`
--

CREATE TABLE `onlinestudents` (
  `id` int(11) NOT NULL,
  `student_code` varchar(50) DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `father_name` varchar(100) DEFAULT NULL,
  `mother_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `blood_group` varchar(10) DEFAULT NULL,
  `mobile` varchar(15) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `pin_code` varchar(10) DEFAULT NULL,
  `block` varchar(100) DEFAULT NULL,
  `post_office` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `district` varchar(100) DEFAULT NULL,
  `study_center` varchar(100) DEFAULT NULL,
  `course_name` varchar(100) DEFAULT NULL,
  `duration` varchar(50) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `reg_amount` decimal(10,2) DEFAULT NULL,
  `payment_mode` varchar(50) DEFAULT NULL,
  `payment_status` varchar(50) DEFAULT NULL,
  `session_start` date DEFAULT NULL,
  `session_end` date DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `id_proof` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `center_id` int(11) DEFAULT NULL,
  `approved` tinyint(4) DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `last_login` datetime DEFAULT NULL,
  `last_active` datetime DEFAULT NULL,
  `grade` varchar(5) DEFAULT NULL,
  `course_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `onlinestudents`
--

INSERT INTO `onlinestudents` (`id`, `student_code`, `username`, `password`, `name`, `father_name`, `mother_name`, `email`, `dob`, `gender`, `blood_group`, `mobile`, `address`, `city`, `pin_code`, `block`, `post_office`, `state`, `district`, `study_center`, `course_name`, `duration`, `price`, `reg_amount`, `payment_mode`, `payment_status`, `session_start`, `session_end`, `photo`, `id_proof`, `created_at`, `updated_at`, `center_id`, `approved`, `status`, `last_login`, `last_active`, `grade`, `course_id`) VALUES
(5, 'STU20260210134611854', 'shivamkumar6725', '$2y$10$QLLo1aXfr9qRXf6c90AUPebankF.4j3AI5qW7fB4Wj.gyINIbk8Kq', 'SHIVAM KUMAR', 'Sanjeet kumar ray', 'Ranju devi', 'shivamyadavpc6074@gmail.com', '2009-01-01', 'Male', '', '6200591095', 'A.t,p.s-SATANPUR', 'SAMASTIPUR', '848132', 'Ujiarpur', 'Satanpur ', 'Bihar', 'Samastipur', 'SIET COMPUTER INSTITUTE, SATANPUR, UJIYARPUR (SAMASTIPUR)-848132', 'ADCA', '1 YEAR', 7500.00, 7500.00, 'Cash', 'Approved', '2026-02-10', '2027-02-10', 'photo_STU20260210134611854.jpg', 'id_STU20260210134611854.jpg', '2026-02-10 05:46:11', '2026-04-28 04:31:10', NULL, 1, 'Active', NULL, NULL, NULL, NULL),
(6, 'STU20260211031639996', 'sudhirkumar8062', '$2y$10$aI8Ee3aOwtfUJIuJgI.0IeMVr7Sar9rEAe6AO1IxTreT6TiN2nCfa', 'SUDHIR KUMAR', 'SHASHI KANT RAY', 'SHILA DEVI', 'sudhir.kumar@gmail.com', '1983-01-01', 'Male', '', '8298469668', 'at+po-Satanpur', 'samastipur', '848132', 'Ujiyarpur', 'SATANPUR', 'Bihar', 'Samastipur', 'SHARNAY INSTITUTE OF COMPUTER CLASSES, Sarairanjan Road, Satanpur-848132', 'ADCA', '1 YEAR', 7500.00, 200.00, 'Cash', 'Approved', '2025-01-11', '2026-01-11', 'photo_STU20260211031639996.jpg', 'id_STU20260211031639996.jpg', '2026-02-10 19:16:40', '2026-02-10 22:27:34', NULL, 1, 'Active', NULL, NULL, 'A', NULL),
(7, 'STU20260221031414902', 'anitakumari6768', '$2y$10$hn8ZBvcqIlgMI/iY1JOQ7OZ4JZanv2WDXQS91kGJR0y6EG5iTb1Dy', 'ANITA KUMARI', 'MAHESH RAY', 'MAHA DEVI', 'anita.kumari@gmail.com', '1996-05-09', 'Female', '', '6299609338', 'at+po-Satanpur', 'samastipur', '848132', 'Ujiyarpur', 'SATANPUR', 'Bihar', 'Samastipur', 'SHARNAY INSTITUTE OF COMPUTER CLASSES, Sarairanjan Road, Satanpur-848132', 'ADCA', '1 YEAR', 7500.00, 200.00, 'Cash', 'Approved', '2025-01-28', '2026-01-28', 'photo_STU20260221031414902.jpg', 'id_STU20260221031414902.jpg', '2026-02-20 19:14:14', '2026-03-04 19:01:56', NULL, 1, 'Active', NULL, NULL, 'A', NULL),
(8, 'STU20260221032025629', 'deepakumari6044', '$2y$10$FvauiGQY097Ng7lr520hGO/gYtvrjHi8yJKOq1hB.G6QGaGQYrkvO', 'DEEPA KUMARI', 'SHAMBHU KANT RAY', 'POONAM DEVI', 'poonam.kumari@gmail.com', '2008-04-08', 'Female', '', '7632848329', 'At+Po-Birnama tula', 'Samastipur', '848134', 'Ujiyarpur', 'Birnama', 'Bihar', 'Samastipur', 'SHARNAY INSTITUTE OF COMPUTER CLASSES, Sarairanjan Road, Satanpur-848132', 'ADCA', '1 YEAR', 7500.00, 500.00, 'Cash', 'Approved', '2025-05-12', '2026-05-12', 'photo_STU20260221032025629.jpg', 'id_STU20260221032025629.jpg', '2026-02-20 19:20:25', '2026-03-04 18:50:15', NULL, 1, 'Active', NULL, NULL, NULL, NULL),
(10, 'STU20260305021542135', 'manishkumar3539', '$2y$10$nq7CV5o/xt1l1rYRa/BccuYwXwgetJH1p4Gh7L0a8oDznbNm8WoPm', 'MANISH KUMAR', 'SAHEB DAS', 'INDU DEVI', 'manish.kumar@gmail.com', '2005-07-23', 'Male', '', '8863050937', 'At-chandchour mathurapur', 'SAMASTIPUR', '848132', 'UJIYARPUR', 'MATHURAPUR', 'Bihar', 'Samastipur', 'SIET COMPUTER INSTITUTE, SATANPUR, UJIYARPUR (SAMASTIPUR)-848132', 'ADCA', '1 YEAR', 7500.00, 500.00, 'Cash', 'Approved', '0000-00-00', '2026-12-10', 'photo_STU20260305021542135.jpg', 'id_STU20260305021542135.jpg', '2026-03-04 18:15:43', '2026-03-04 18:50:40', NULL, 1, 'Active', NULL, NULL, NULL, NULL),
(11, 'STU20260305024323755', 'anitakumari6760', '$2y$10$V2Z1flzrddAgJvJZz4Eq3OHJoVznaBFNuqUUGQDUYSrtKOCGkpb1m', 'ANITA KUMARI', 'MAHESH RAY', 'MAHA DEVI', 'anita.kumari@gmail.com', '1996-05-09', 'Female', '', '6299609338', 'at+po-Satanpur', 'samastipur', '848132', 'UJIYARPUR', 'SATANPUR', 'Bihar', 'Samastipur', 'SIET COMPUTER INSTITUTE, SATANPUR, UJIYARPUR (SAMASTIPUR)-848132', 'ADCA', '1 YEAR', 7500.00, 500.00, 'Cash', 'Approved', '2005-01-28', '2026-01-28', 'photo_STU20260305024323755.jpg', 'id_STU20260305024323755.jpg', '2026-03-04 18:43:23', '2026-05-12 17:06:34', NULL, 1, 'Active', NULL, NULL, NULL, NULL),
(12, 'STU20260310031753485', 'rokhsarfirdaus6852', '$2y$10$4MRRjAptAaVOeARy9pakb.SFxsiXAUsLd9xDowQHiuR8g.V2i.rBO', 'ROKHSAR FIRDAUS', 'MD FIROZ', 'ROKHSHANA KHATOON', 'rokharfirdaus123@gmail.com', '2008-09-17', 'Female', '', '6205443898', 'at+po-Satanpur', 'samastipur', '848132', '', 'SATANPUR', 'Bihar', 'Samastipur', 'SHARNAY INSTITUTE OF COMPUTER CLASSES, Sarairanjan Road, Satanpur-848132', 'ADCA', '1 YEAR', 7500.00, 500.00, 'Cash', 'Approved', '2026-03-07', '2027-03-07', 'photo_STU20260310031753485.jpeg', 'id_STU20260310031753485.jpeg', '2026-03-09 20:17:53', '2026-04-28 04:29:30', NULL, 1, 'Active', NULL, NULL, NULL, NULL),
(13, 'STU20260310032031105', 'rokhsarfirdaus6843', '$2y$10$SOYV1W0aLhZKyhbyQ/m5NeL32Idj5dNAHAXwG46HSfqfHJ84RbQDm', 'ROKHSAR FIRDAUS', 'MD FIROZ', 'ROKHSHANA KHATOON', 'rokharfirdaus123@gmail.com', '2008-09-17', 'Female', '', '6205443898', 'at+po-Satanpur', 'samastipur', '848132', '', 'SATANPUR', 'Bihar', 'Samastipur', 'SHARNAY INSTITUTE OF COMPUTER CLASSES, Sarairanjan Road, Satanpur-848132', 'ADCA', '1 YEAR', 7500.00, 500.00, 'Cash', 'Approved', '2026-03-07', '2027-03-07', 'photo_STU20260310032031105.jpeg', 'id_STU20260310032031105.jpeg', '2026-03-09 20:20:31', '2026-05-13 00:02:10', NULL, 1, 'Active', NULL, NULL, NULL, NULL),
(14, 'STU20260310032544875', 'rohitkumar2129', '$2y$10$V5WEPe3WkfXlCmtNEfmlhujMBUnDbyN2hv7cSTctgwr1.77PlvtCe', 'ROHIT KUMAR ', 'ARJUN DAS ', 'TARA DEVI ', 'rohitmathurapur80@gmail.com', '2007-10-29', 'Male', '', '8521056823', 'chandchaur mathurapur', 'mathurapur', '848132', 'mathurapur', 'MATHURAPUR', 'Bihar', 'Samastipur', 'SHARNAY INSTITUTE OF COMPUTER CLASSES, Sarairanjan Road, Satanpur-848132', 'ADCA', '1 YEAR', 7500.00, 500.00, 'Cash', 'Approved', '2025-12-17', '2026-12-17', 'photo_STU20260310032544875.jpeg', 'id_STU20260310032544875.jpeg', '2026-03-09 20:25:44', '2026-04-28 04:28:00', NULL, 1, 'Active', NULL, NULL, NULL, NULL),
(15, 'STU20260310032631270', 'rohitkumar6775', '$2y$10$cOQUUbMVfZypVnAaVYmUoOWvzFiawRaFjMVBnWIEU7pw0vzvLaYM2', 'ROHIT KUMAR ', 'ARJUN DAS ', 'TARA DEVI ', 'rohitmathurapur80@gmail.com', '2007-10-29', 'Male', '', '8521056823', 'chandchaur mathurapur', 'mathurapur', '848132', 'mathurapur', 'MATHURAPUR', 'Bihar', 'Samastipur', 'SHARNAY INSTITUTE OF COMPUTER CLASSES, Sarairanjan Road, Satanpur-848132', 'ADCA', '1 YEAR', 7500.00, 500.00, 'Cash', 'Approved', '2025-12-17', '2026-12-17', 'photo_STU20260310032631270.jpeg', 'id_STU20260310032631270.jpeg', '2026-03-09 20:26:31', '2026-05-11 02:52:00', NULL, 1, 'Active', NULL, NULL, NULL, NULL),
(16, 'STU20260313120115800', 'mukeshkumar5999', '$2y$10$O.LtPDacRWbWxpbuGZ3Cz.iXIVWt.gOVrrx5oX3svHTCbJZ7/86ra', 'MUKESH KUMAR', 'MANCHIT MAHTO', 'SUNAINA DEVI', 'mukesh.kr9523@gmail.com', '1998-02-15', 'Male', 'A+', '9523534412', 'AT - DIHULI PO PS - ANGARGHAT\\r\\nDIST - SAMASTIPUR', 'Samastipur', '848236', 'UJIYARPUR', 'ANGARGHAT', 'Bihar', 'Samastipur', 'Unique computer classes, At - Dihuli Po+Ps-Anharghat Dist-samastipur  Pin code - 848236', 'ADCA', '1 YEAR', 7500.00, 200.00, 'Wallet', 'Approved', '2025-01-01', '2026-01-01', 'photo_STU20260313120115800.jpg', 'id_STU20260313120115800.pdf', '2026-03-13 05:01:15', '2026-03-17 20:29:28', 7, 1, 'Active', NULL, NULL, 'A+', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
  `id` int(11) NOT NULL,
  `question_paper_id` int(11) DEFAULT NULL,
  `question_text` text NOT NULL,
  `question_type` enum('MCQ','True_False','Short_Answer') DEFAULT 'MCQ',
  `marks` int(11) DEFAULT 1,
  `difficulty_level` enum('Easy','Medium','Hard') DEFAULT 'Medium',
  `serial_number` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `question_options`
--

CREATE TABLE `question_options` (
  `id` int(11) NOT NULL,
  `question_id` int(11) DEFAULT NULL,
  `option_text` text NOT NULL,
  `is_correct` tinyint(1) DEFAULT 0,
  `option_letter` char(1) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `question_papers`
--

CREATE TABLE `question_papers` (
  `id` int(11) NOT NULL,
  `exam_id` int(11) DEFAULT NULL,
  `center_id` int(11) DEFAULT NULL,
  `paper_name` varchar(255) NOT NULL,
  `total_questions` int(11) DEFAULT NULL,
  `marks_per_question` int(11) DEFAULT 1,
  `negative_marking` decimal(3,2) DEFAULT 0.25,
  `instructions` text DEFAULT NULL,
  `status` enum('Draft','Published','Archived') DEFAULT 'Draft',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `states`
--

CREATE TABLE `states` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `states`
--

INSERT INTO `states` (`id`, `name`) VALUES
(1, 'Andhra Pradesh'),
(2, 'Arunachal Pradesh'),
(3, 'Assam'),
(4, 'Bihar'),
(5, 'Chhattisgarh'),
(6, 'Goa'),
(7, 'Gujarat'),
(8, 'Haryana'),
(9, 'Himachal Pradesh'),
(10, 'Jharkhand'),
(11, 'Karnataka'),
(12, 'Kerala'),
(13, 'Madhya Pradesh'),
(14, 'Maharashtra'),
(15, 'Manipur'),
(16, 'Meghalaya'),
(17, 'Mizoram'),
(18, 'Nagaland'),
(19, 'Odisha'),
(20, 'Punjab'),
(21, 'Rajasthan'),
(22, 'Sikkim'),
(23, 'Tamil Nadu'),
(24, 'Telangana'),
(25, 'Tripura'),
(26, 'Uttar Pradesh'),
(27, 'Uttarakhand'),
(28, 'West Bengal'),
(29, 'Andaman and Nicobar Islands'),
(30, 'Chandigarh'),
(31, 'Dadra and Nagar Haveli and Daman and Diu'),
(32, 'Delhi'),
(33, 'Jammu and Kashmir'),
(34, 'Ladakh'),
(35, 'Lakshadweep'),
(36, 'Puducherry');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `state` varchar(100) DEFAULT NULL,
  `district` varchar(100) DEFAULT NULL,
  `study_center` varchar(100) DEFAULT NULL,
  `course_name` varchar(100) DEFAULT NULL,
  `duration` varchar(100) DEFAULT NULL,
  `price` varchar(100) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `father_name` varchar(100) DEFAULT NULL,
  `mother_name` varchar(255) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `blood_group` varchar(255) DEFAULT NULL,
  `mobile` varchar(15) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `pin_code` varchar(10) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `block` varchar(255) DEFAULT NULL,
  `post_office` varchar(255) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `id_proof` varchar(255) DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `reg_amount` decimal(10,2) DEFAULT NULL,
  `payment_mode` varchar(20) DEFAULT NULL,
  `payment_status` varchar(50) DEFAULT NULL,
  `student_code` varchar(20) DEFAULT NULL,
  `approved` tinyint(1) DEFAULT 0,
  `session_start` date DEFAULT NULL,
  `session_end` date DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `state`, `district`, `study_center`, `course_name`, `duration`, `price`, `name`, `father_name`, `mother_name`, `email`, `dob`, `gender`, `blood_group`, `mobile`, `address`, `pin_code`, `city`, `block`, `post_office`, `photo`, `id_proof`, `username`, `password`, `reg_amount`, `payment_mode`, `payment_status`, `student_code`, `approved`, `session_start`, `session_end`, `created_at`) VALUES
(2, '4', '96', 'SHARNAY INSTITUTE OF COMPUTER CLASSES, Sarairanjan Road, Satanpur-848132', 'ADCA', '1 YEAR', '7500.00', 'ROKHSAR FIRDAUS', 'MD FIROZ', 'ROKHSHANA KHATOON', 'rokharfirdaus123@gmail.com', '2008-09-17', 'Female', '', '6205443898', 'at+po-Satanpur', '848132', 'samastipur', 'UJIYARPUR', 'SATANPUR', 'WhatsApp Image 2026-03-10 at 10.09.47 AM.jpeg', 'WhatsApp Image 2026-03-10 at 10.09.47 AM.jpeg', '6205443898', '$2y$10$WxeTokscSc2Lk7po7hspLeWd1q9/OPq0kQm.Jo5AM6sJm3Ql.nr/C', 200.00, 'Offline', 'Pending (Offline)', 'SIET20260001', 0, NULL, NULL, '2026-03-10 03:11:02');

-- --------------------------------------------------------

--
-- Table structure for table `student_marks`
--

CREATE TABLE `student_marks` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `student_code` varchar(50) NOT NULL,
  `subject_name` varchar(100) NOT NULL,
  `max_marks` int(11) NOT NULL DEFAULT 100,
  `obtained_marks` int(11) NOT NULL,
  `percentage` decimal(5,2) NOT NULL,
  `grade` varchar(10) NOT NULL,
  `remarks` text DEFAULT NULL,
  `exam_date` date NOT NULL,
  `added_by` varchar(100) DEFAULT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_module_marks`
--

CREATE TABLE `student_module_marks` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL,
  `written_marks` int(11) DEFAULT 0,
  `practical_marks` int(11) DEFAULT 0,
  `project_marks` int(11) DEFAULT 0,
  `viva_marks` int(11) DEFAULT 0,
  `total_marks` int(11) DEFAULT 0,
  `percentage` decimal(5,2) DEFAULT 0.00,
  `grade` varchar(10) DEFAULT NULL,
  `exam_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_notifications`
--

CREATE TABLE `student_notifications` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text DEFAULT NULL,
  `type` enum('info','success','warning','danger') DEFAULT 'info',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `id` int(11) NOT NULL,
  `teacher_code` varchar(50) NOT NULL,
  `adv_id` varchar(50) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `father_name` varchar(100) NOT NULL,
  `mother_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `blood_group` varchar(10) DEFAULT NULL,
  `mobile` varchar(15) DEFAULT NULL,
  `state_id` int(11) DEFAULT NULL,
  `district_id` int(11) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `pin_code` varchar(10) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `block` varchar(100) DEFAULT NULL,
  `post_office` varchar(100) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `id_proof` varchar(255) DEFAULT NULL,
  `allot_center` varchar(50) DEFAULT NULL,
  `teacher_status` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `center_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teachers`
--

INSERT INTO `teachers` (`id`, `teacher_code`, `adv_id`, `name`, `father_name`, `mother_name`, `email`, `dob`, `gender`, `blood_group`, `mobile`, `state_id`, `district_id`, `address`, `pin_code`, `city`, `block`, `post_office`, `photo`, `id_proof`, `allot_center`, `teacher_status`, `created_at`, `center_id`) VALUES
(1, 'SIET-TEACH2026-0001', NULL, 'SANJEET KUMAR', 'RAM CHANDRA RAY', 'RAM JYOTI DEVI', 'sanjeetkumar122853@gmail.com', '1985-02-12', 'Male', '', '7808224312', 4, 96, 'at+po-satanpur', '848132', 'samastipur', 'UJIYARPUR', 'ujiyarpur', '697c90736c006_mukesh ji.jpeg', '697c907370a8f_mukesh ji.jpeg', 'SHARNAY INSTITUTE OF COMPUTER CLASSES, Sarairanjan', 1, '2026-01-30 11:05:23', NULL),
(9, 'TCH20260217797', '1', 'Deepak Kumar', 'sanjay prsad', 'Katrina kaif', 'Deepakkumar933041@gmail.com', '1993-06-16', 'Male', 'A', '7526972793', 4, 90, 'Khorampur', '8013303', 'islampur', 'islampur', 'khorampur', '1771317581_Screenshot (7).png', '1771317581_Screenshot (12).png', 'SIET COMPUTER INSTITUTE, SATANPUR, UJIYARPUR (SAMA', 1, '2026-02-17 08:39:41', 3);

-- --------------------------------------------------------

--
-- Table structure for table `teacher_education`
--

CREATE TABLE `teacher_education` (
  `id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `education` varchar(100) DEFAULT NULL,
  `session_from` varchar(10) DEFAULT NULL,
  `session_to` varchar(10) DEFAULT NULL,
  `total_marks` int(11) DEFAULT NULL,
  `obt_marks` int(11) DEFAULT NULL,
  `percentage` varchar(10) DEFAULT NULL,
  `grade` varchar(10) DEFAULT NULL,
  `marksheet` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teacher_education`
--

INSERT INTO `teacher_education` (`id`, `teacher_id`, `education`, `session_from`, `session_to`, `total_marks`, `obt_marks`, `percentage`, `grade`, `marksheet`, `created_at`) VALUES
(9, 9, 'B.Com', '2016', '2019', 1200, 1000, '7.9', 'a', '1771317581_0_Screenshot (8).png', '2026-02-17 08:39:42'),
(10, 1, 'BCA', '2010', '2014', 1100, 544, '60', 'A', '', '2026-02-21 03:59:05');

-- --------------------------------------------------------

--
-- Table structure for table `teacher_experience`
--

CREATE TABLE `teacher_experience` (
  `id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `org` varchar(150) DEFAULT NULL,
  `role` varchar(100) DEFAULT NULL,
  `experience_from` varchar(10) DEFAULT NULL,
  `experience_to` varchar(10) DEFAULT NULL,
  `experience_doc` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teacher_experience`
--

INSERT INTO `teacher_experience` (`id`, `teacher_id`, `org`, `role`, `experience_from`, `experience_to`, `experience_doc`, `created_at`) VALUES
(2, 9, 'rkv it solutions', 'teachers', '2015', '2025', '1771317582_0_Screenshot (6).png', '2026-02-17 08:39:42'),
(3, 1, 's', '', '', '', '', '2026-02-21 03:59:05');

-- --------------------------------------------------------

--
-- Table structure for table `vacancy`
--

CREATE TABLE `vacancy` (
  `id` int(11) NOT NULL,
  `adv_id` varchar(50) NOT NULL,
  `post` varchar(100) DEFAULT NULL,
  `state_id` int(11) DEFAULT NULL,
  `district_id` int(11) DEFAULT NULL,
  `eligibility` varchar(255) DEFAULT NULL,
  `experience` varchar(100) DEFAULT NULL,
  `no_of_post` varchar(50) DEFAULT NULL,
  `start_date` varchar(50) DEFAULT NULL,
  `end_date` varchar(50) DEFAULT NULL,
  `document` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wallet_transactions`
--

CREATE TABLE `wallet_transactions` (
  `id` int(11) NOT NULL,
  `center_id` int(11) NOT NULL,
  `transaction_type` enum('credit','debit','registration_fee') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `transaction_id` varchar(100) NOT NULL,
  `status` enum('success','failed','pending') DEFAULT 'pending',
  `description` text DEFAULT NULL,
  `reference_id` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `zone`
--

CREATE TABLE `zone` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `blocks`
--
ALTER TABLE `blocks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `center_details`
--
ALTER TABLE `center_details`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `center_code` (`center_code`),
  ADD UNIQUE KEY `unique_username` (`username`),
  ADD KEY `idx_member_id` (`member_id`);

--
-- Indexes for table `center_payment_methods`
--
ALTER TABLE `center_payment_methods`
  ADD PRIMARY KEY (`id`),
  ADD KEY `center_id` (`center_id`);

--
-- Indexes for table `center_wallet`
--
ALTER TABLE `center_wallet`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `center_id` (`center_id`);

--
-- Indexes for table `certificates`
--
ALTER TABLE `certificates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `course_code` (`course_code`);

--
-- Indexes for table `course_subjects`
--
ALTER TABLE `course_subjects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `districts`
--
ALTER TABLE `districts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `state_id` (`state_id`);

--
-- Indexes for table `exam_schedule`
--
ALTER TABLE `exam_schedule`
  ADD PRIMARY KEY (`id`),
  ADD KEY `center_id` (`center_id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `module_id` (`module_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `member_information`
--
ALTER TABLE `member_information`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `modules`
--
ALTER TABLE `modules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `onlinestudents`
--
ALTER TABLE `onlinestudents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_code` (`student_code`);

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `question_paper_id` (`question_paper_id`);

--
-- Indexes for table `question_options`
--
ALTER TABLE `question_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `question_id` (`question_id`);

--
-- Indexes for table `question_papers`
--
ALTER TABLE `question_papers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `exam_id` (`exam_id`),
  ADD KEY `center_id` (`center_id`);

--
-- Indexes for table `states`
--
ALTER TABLE `states`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_code` (`student_code`);

--
-- Indexes for table `student_marks`
--
ALTER TABLE `student_marks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `student_code` (`student_code`);

--
-- Indexes for table `student_module_marks`
--
ALTER TABLE `student_module_marks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_student_module` (`student_id`,`module_id`),
  ADD KEY `module_id` (`module_id`);

--
-- Indexes for table `student_notifications`
--
ALTER TABLE `student_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `teacher_code` (`teacher_code`),
  ADD KEY `fk_teachers_center` (`center_id`);

--
-- Indexes for table `teacher_education`
--
ALTER TABLE `teacher_education`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Indexes for table `teacher_experience`
--
ALTER TABLE `teacher_experience`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Indexes for table `vacancy`
--
ALTER TABLE `vacancy`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `adv_id` (`adv_id`);

--
-- Indexes for table `wallet_transactions`
--
ALTER TABLE `wallet_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `center_id` (`center_id`),
  ADD KEY `transaction_id` (`transaction_id`);

--
-- Indexes for table `zone`
--
ALTER TABLE `zone`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `blocks`
--
ALTER TABLE `blocks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=604;

--
-- AUTO_INCREMENT for table `center_details`
--
ALTER TABLE `center_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `center_payment_methods`
--
ALTER TABLE `center_payment_methods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `center_wallet`
--
ALTER TABLE `center_wallet`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `certificates`
--
ALTER TABLE `certificates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `course_subjects`
--
ALTER TABLE `course_subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `districts`
--
ALTER TABLE `districts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=737;

--
-- AUTO_INCREMENT for table `exam_schedule`
--
ALTER TABLE `exam_schedule`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `member_information`
--
ALTER TABLE `member_information`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `modules`
--
ALTER TABLE `modules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `onlinestudents`
--
ALTER TABLE `onlinestudents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `question_options`
--
ALTER TABLE `question_options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `question_papers`
--
ALTER TABLE `question_papers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `states`
--
ALTER TABLE `states`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `student_marks`
--
ALTER TABLE `student_marks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_module_marks`
--
ALTER TABLE `student_module_marks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `student_notifications`
--
ALTER TABLE `student_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `teacher_education`
--
ALTER TABLE `teacher_education`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `teacher_experience`
--
ALTER TABLE `teacher_experience`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `vacancy`
--
ALTER TABLE `vacancy`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wallet_transactions`
--
ALTER TABLE `wallet_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `zone`
--
ALTER TABLE `zone`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `center_payment_methods`
--
ALTER TABLE `center_payment_methods`
  ADD CONSTRAINT `center_payment_methods_ibfk_1` FOREIGN KEY (`center_id`) REFERENCES `center_details` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `center_wallet`
--
ALTER TABLE `center_wallet`
  ADD CONSTRAINT `center_wallet_ibfk_1` FOREIGN KEY (`center_id`) REFERENCES `center_details` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `districts`
--
ALTER TABLE `districts`
  ADD CONSTRAINT `districts_ibfk_1` FOREIGN KEY (`state_id`) REFERENCES `states` (`id`);

--
-- Constraints for table `exam_schedule`
--
ALTER TABLE `exam_schedule`
  ADD CONSTRAINT `exam_schedule_ibfk_1` FOREIGN KEY (`center_id`) REFERENCES `center_details` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_schedule_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_schedule_ibfk_3` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_schedule_ibfk_4` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `modules`
--
ALTER TABLE `modules`
  ADD CONSTRAINT `modules_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`question_paper_id`) REFERENCES `question_papers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `question_options`
--
ALTER TABLE `question_options`
  ADD CONSTRAINT `question_options_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `question_papers`
--
ALTER TABLE `question_papers`
  ADD CONSTRAINT `question_papers_ibfk_1` FOREIGN KEY (`exam_id`) REFERENCES `exam_schedule` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `question_papers_ibfk_2` FOREIGN KEY (`center_id`) REFERENCES `center_details` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_marks`
--
ALTER TABLE `student_marks`
  ADD CONSTRAINT `student_marks_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `onlinestudents` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_module_marks`
--
ALTER TABLE `student_module_marks`
  ADD CONSTRAINT `student_module_marks_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `onlinestudents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_module_marks_ibfk_2` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_notifications`
--
ALTER TABLE `student_notifications`
  ADD CONSTRAINT `student_notifications_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `onlinestudents` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `teachers`
--
ALTER TABLE `teachers`
  ADD CONSTRAINT `fk_teachers_center` FOREIGN KEY (`center_id`) REFERENCES `center_details` (`id`);

--
-- Constraints for table `teacher_education`
--
ALTER TABLE `teacher_education`
  ADD CONSTRAINT `teacher_education_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `teacher_experience`
--
ALTER TABLE `teacher_experience`
  ADD CONSTRAINT `teacher_experience_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wallet_transactions`
--
ALTER TABLE `wallet_transactions`
  ADD CONSTRAINT `wallet_transactions_ibfk_1` FOREIGN KEY (`center_id`) REFERENCES `center_details` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
