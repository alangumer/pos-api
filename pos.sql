-- phpMyAdmin SQL Dump
-- version 4.6.0
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Apr 19, 2016 at 05:09 PM
-- Server version: 5.6.28-0ubuntu0.14.04.1
-- PHP Version: 5.5.9-1ubuntu4.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pos`
--

-- --------------------------------------------------------

--
-- Table structure for table `Allocation`
--

CREATE TABLE `Allocation` (
  `id` int(11) NOT NULL,
  `payment_id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `type` varchar(20) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `Allocation`
--

INSERT INTO `Allocation` (`id`, `payment_id`, `invoice_id`, `amount`, `type`, `date`) VALUES
(1, 0, 1, 705.00, 'receivable', '2016-04-19 17:57:58'),
(2, 1, 1, -205.00, 'paid', '2016-04-19 17:57:58'),
(3, 0, 2, 1000.00, 'receivable', '2016-04-19 18:10:23'),
(4, 2, 2, -700.00, 'paid', '2016-04-19 18:10:23'),
(5, 0, 3, 200.00, 'receivable', '2016-04-19 18:12:44'),
(6, 0, 4, 120.00, 'receivable', '2016-04-19 18:48:46'),
(7, 0, 5, 355.00, 'receivable', '2016-04-19 18:59:16'),
(8, 0, 6, 10.00, 'receivable', '2016-04-19 19:02:11'),
(9, 0, 7, 10.00, 'receivable', '2016-04-19 19:06:41');

-- --------------------------------------------------------

--
-- Table structure for table `Bank`
--

CREATE TABLE `Bank` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `Bank`
--

INSERT INTO `Bank` (`id`, `name`) VALUES
(1, 'Banco A'),
(2, 'Banco B'),
(3, 'Banco C'),
(4, 'Banco D');

-- --------------------------------------------------------

--
-- Table structure for table `Category`
--

CREATE TABLE `Category` (
  `id` int(11) NOT NULL,
  `name` varchar(45) NOT NULL,
  `status` int(11) NOT NULL DEFAULT '1',
  `parent_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `Category`
--

INSERT INTO `Category` (`id`, `name`, `status`, `parent_id`) VALUES
(1, 'Categoria A', 1, NULL),
(2, 'Categoria B', 1, NULL),
(3, 'Categoria C', 0, NULL),
(4, 'Categoria D', 0, NULL),
(5, 'Categoria E', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `Customer`
--

CREATE TABLE `Customer` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `nit` varchar(100) NOT NULL,
  `tel` varchar(45) NOT NULL,
  `address` varchar(250) NOT NULL,
  `birthdate` date NOT NULL,
  `register_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` int(11) NOT NULL DEFAULT '1',
  `customer_type_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `Customer`
--

INSERT INTO `Customer` (`id`, `name`, `nit`, `tel`, `address`, `birthdate`, `register_date`, `status`, `customer_type_id`) VALUES
(1, 'jhjk', '5456454', '65675675', 'ciudad', '2016-02-10', '2016-02-18 07:30:56', 0, 1),
(2, 'jhjk', '5456454', '65675675', 'ciudad', '2016-02-10', '2016-02-18 07:37:14', 0, 1),
(3, 'jhjk', '5456454', '65675675', 'ciudad', '2016-02-10', '2016-02-18 07:43:11', 0, 1),
(4, 'jhjk', '5456454', '65675675', 'ciudad', '2016-02-10', '2016-02-18 07:43:59', 0, 1),
(5, 'jhjk', '5456454', '65675675', 'ciudad', '2016-02-10', '2016-02-18 07:49:54', 0, 1),
(6, 'Juan', '9809890', '8767867', 'ghjhg', '0000-00-00', '2016-02-18 08:05:01', 0, 1),
(7, 'Juan', '90898098', '4345345', 'Ciudad', '0000-00-00', '2016-02-18 08:06:12', 0, 1),
(8, 'Juan', '9809898', '97897897', 'Cuydad', '0000-00-00', '2016-02-18 08:06:44', 0, 1),
(9, 'Eliseooooo', '8799879878', '8767868767', 'Ciudad', '2016-02-20', '2016-02-19 07:17:46', 0, 2),
(10, 'Cristian', '89798798789', '6576576565', 'Ciudad', '2016-02-24', '2016-02-19 07:25:28', 0, 2),
(11, 'Luis Enrique Gomez', '89798798798', '7676786', 'Ciudad', '2000-02-10', '2016-02-19 07:27:10', 0, 2),
(12, 'Amanda', '879879878', '89789798789', 'Santa Rosa', '2016-02-24', '2016-02-19 07:36:24', 0, 1),
(13, 'Henry', '897897987', '897897897897', 'Ciudad', '2016-02-06', '2016-02-19 07:37:22', 0, 2),
(14, 'Jeremiassss', '79879879878', '789798798798', 'Ciudad', '2016-02-18', '2016-02-19 08:05:08', 0, 1),
(15, 'Baldir', '79879877', '56765765', 'Santa Lucia', '2016-02-22', '2016-02-22 03:33:47', 0, 1),
(16, 'Efrain', '878978978', '897987897', 'Ciudad', '2016-02-10', '2016-02-22 05:09:49', 0, 2),
(17, 'Nicolas', '7987897', '87786767', 'Ciudad', '2016-02-23', '2016-02-24 07:49:10', 0, 2);

-- --------------------------------------------------------

--
-- Table structure for table `CustomerType`
--

CREATE TABLE `CustomerType` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `credit_limit` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `CustomerType`
--

INSERT INTO `CustomerType` (`id`, `name`, `credit_limit`) VALUES
(1, 'Tipo 1', 1000),
(2, 'Tipo 2', 1500),
(3, 'Tipo 3', 1000),
(4, 'Tipo 4', 1000);

-- --------------------------------------------------------

--
-- Table structure for table `Invoice`
--

CREATE TABLE `Invoice` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `ref` varchar(20) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `Invoice`
--

INSERT INTO `Invoice` (`id`, `customer_id`, `ref`, `date`) VALUES
(1, 17, '', '2016-04-19 17:57:58'),
(2, 17, '', '2016-04-19 18:10:23'),
(3, 17, '', '2016-04-19 18:12:44'),
(4, 17, '', '2016-04-19 18:48:46'),
(5, 17, '', '2016-04-19 18:59:16'),
(6, 17, '', '2016-04-19 19:02:11'),
(7, 17, '', '2016-04-19 19:06:41');

-- --------------------------------------------------------

--
-- Table structure for table `InvoiceDetail`
--

CREATE TABLE `InvoiceDetail` (
  `id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `discount` decimal(5,2) DEFAULT NULL,
  `iva` decimal(5,2) NOT NULL,
  `total` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `InvoiceDetail`
--

INSERT INTO `InvoiceDetail` (`id`, `invoice_id`, `product_id`, `quantity`, `price`, `discount`, `iva`, `total`) VALUES
(1, 1, 3, 3, 235.00, 0.00, 0.00, 705.00),
(2, 2, 3, 5, 200.00, 0.00, 0.00, 1000.00),
(3, 3, 3, 1, 200.00, 0.00, 0.00, 200.00),
(4, 4, 4, 1, 120.00, 0.00, 0.00, 120.00),
(5, 5, 4, 1, 120.00, 0.00, 0.00, 120.00),
(6, 5, 3, 1, 235.00, 0.00, 0.00, 235.00),
(7, 6, 4, 1, 10.00, 0.00, 0.00, 10.00),
(8, 7, 4, 1, 10.00, 0.00, 0.00, 10.00);

-- --------------------------------------------------------

--
-- Table structure for table `Payment`
--

CREATE TABLE `Payment` (
  `id` int(11) NOT NULL,
  `ref` varchar(20) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `Payment`
--

INSERT INTO `Payment` (`id`, `ref`, `amount`, `date`) VALUES
(1, '', 205.00, '2016-04-19 17:57:58'),
(2, '', 700.00, '2016-04-19 18:10:23');

-- --------------------------------------------------------

--
-- Table structure for table `Product`
--

CREATE TABLE `Product` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `status` int(11) NOT NULL DEFAULT '1',
  `stock` int(11) NOT NULL,
  `minimum_amount` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `price` decimal(5,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `Product`
--

INSERT INTO `Product` (`id`, `name`, `status`, `stock`, `minimum_amount`, `category_id`, `price`) VALUES
(1, 'Producto 1', 1, 700, 10, 2, 25.00),
(2, 'Producto 2', 1, 2000, 78, 2, 989.00),
(3, 'Producto 3', 1, 878, 12, 1, 235.00),
(4, 'Producto 4', 1, 1000, 65, 2, 120.00);

-- --------------------------------------------------------

--
-- Table structure for table `Provider`
--

CREATE TABLE `Provider` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `tel` varchar(20) NOT NULL,
  `contact` varchar(100) NOT NULL,
  `address` varchar(250) NOT NULL,
  `nit` varchar(100) NOT NULL,
  `status` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `Provider`
--

INSERT INTO `Provider` (`id`, `name`, `tel`, `contact`, `address`, `nit`, `status`) VALUES
(1, 'Las margaritas', '7656756756', 'Julian Diaz Perez', 'San Juan', '89898989', 1);

-- --------------------------------------------------------

--
-- Table structure for table `Store`
--

CREATE TABLE `Store` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `tel` int(20) NOT NULL,
  `address` varchar(100) NOT NULL,
  `registered_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `Store`
--

INSERT INTO `Store` (`id`, `name`, `tel`, `address`, `registered_date`, `status`) VALUES
(1, 'Agropecuaria ABC', 56786456, 'San Lucas', '2016-02-29 04:03:50', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `Allocation`
--
ALTER TABLE `Allocation`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `Bank`
--
ALTER TABLE `Bank`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `Category`
--
ALTER TABLE `Category`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `Customer`
--
ALTER TABLE `Customer`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `CustomerType`
--
ALTER TABLE `CustomerType`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `Invoice`
--
ALTER TABLE `Invoice`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `InvoiceDetail`
--
ALTER TABLE `InvoiceDetail`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `Payment`
--
ALTER TABLE `Payment`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `Product`
--
ALTER TABLE `Product`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `Provider`
--
ALTER TABLE `Provider`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `Store`
--
ALTER TABLE `Store`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `Allocation`
--
ALTER TABLE `Allocation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
--
-- AUTO_INCREMENT for table `Bank`
--
ALTER TABLE `Bank`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `Category`
--
ALTER TABLE `Category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT for table `Customer`
--
ALTER TABLE `Customer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;
--
-- AUTO_INCREMENT for table `CustomerType`
--
ALTER TABLE `CustomerType`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `Invoice`
--
ALTER TABLE `Invoice`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
--
-- AUTO_INCREMENT for table `InvoiceDetail`
--
ALTER TABLE `InvoiceDetail`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
--
-- AUTO_INCREMENT for table `Payment`
--
ALTER TABLE `Payment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `Product`
--
ALTER TABLE `Product`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `Provider`
--
ALTER TABLE `Provider`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `Store`
--
ALTER TABLE `Store`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
