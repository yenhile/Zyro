-- phpMyAdmin SQL Dump
-- version 2.11.6
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Apr 23, 2025 at 03:06 AM
-- Server version: 5.0.51
-- PHP Version: 5.2.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `tmdt_db`
--
CREATE DATABASE IF NOT EXISTS `tmdt_db` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
USE `tmdt_db`;

-- --------------------------------------------------------

--
-- Table structure for table `congty`
--

CREATE TABLE IF NOT EXISTS `congty` (
  `idcty` int(10) unsigned NOT NULL auto_increment,
  `tencty` varchar(100) character set utf8 collate utf8_unicode_ci NOT NULL,
  `diachi` varchar(200) character set utf8 collate utf8_unicode_ci NOT NULL,
  `dienthoai` varchar(20) character set utf8 collate utf8_unicode_ci NOT NULL,
  `fax` varchar(20) character set utf8 collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`idcty`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `congty`
--

INSERT INTO `congty` (`tencty`, `diachi`, `dienthoai`, `fax`) VALUES
('Samsung', 'korea', '+999', '100000'),
('Apple', 'American', '999999998', '100000'),
('Future', 'Sun', '999999998', '100000');

-- --------------------------------------------------------

--
-- Table structure for table `dathang`
--

CREATE TABLE IF NOT EXISTS `dathang` (
  `iddh` int(10) unsigned NOT NULL auto_increment,
  `idkh` int(11) NOT NULL,
  `id_nhanvien` int(11) NOT NULL,
  `ngaydathang` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `trangthai` varchar(100) character set utf8 collate utf8_unicode_ci NOT NULL default '0',
  PRIMARY KEY  (`iddh`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

--
-- Dumping data for table `dathang`
--

INSERT INTO `dathang` (`iddh`, `idkh`, `id_nhanvien`, `ngaydathang`, `trangthai`) VALUES
(2, 11, 0, '2025-04-19 12:33:14', '0'),
(3, 12, 0, '2025-04-20 09:54:22', '0'),
(4, 12, 0, '2025-04-20 10:32:25', '0'),
(5, 12, 0, '2025-04-20 10:37:06', '0');

-- --------------------------------------------------------

--
-- Table structure for table `dathang_chitiet`
--

CREATE TABLE IF NOT EXISTS `dathang_chitiet` (
  `iddh` int(10) unsigned NOT NULL auto_increment,
  `idsp` int(10) unsigned NOT NULL,
  `soluong` int(11) NOT NULL,
  `dongia` float NOT NULL,
  `giamgia` varchar(200) character set utf8 collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`iddh`,`idsp`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

--
-- Dumping data for table `dathang_chitiet`
--

INSERT INTO `dathang_chitiet` (`iddh`, `idsp`, `soluong`, `dongia`, `giamgia`) VALUES
(2, 1, 2, 500000, 'giảm giá 5000000'),
(3, 1, 3, 500000, 'giảm giá 5000000'),
(4, 1, 1, 500000, 'giảm giá 5000000'),
(5, 5, 1, 0, 'free trọn đời');

-- --------------------------------------------------------

--
-- Table structure for table `khachhang`
--

CREATE TABLE IF NOT EXISTS `khachhang` (
  `idkh` int(10) unsigned NOT NULL,
  `email` varchar(50) character set utf8 NOT NULL,
  `password` varchar(255) character set utf8 NOT NULL,
  `hodem` varchar(50) character set utf8 NOT NULL,
  `ten` varchar(20) character set utf8 NOT NULL,
  `diachi` varchar(200) character set utf8 NOT NULL,
  `diachinhanhang` varchar(200) character set utf8 NOT NULL,
  `dienthoai` varchar(20) character set utf8 NOT NULL,
  PRIMARY KEY  (`idkh`),
  UNIQUE KEY `email` (`email`,`dienthoai`),
  KEY `idkh` (`idkh`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `khachhang`
--

INSERT INTO `khachhang` (`idkh`, `email`, `password`, `hodem`, `ten`, `diachi`, `diachinhanhang`, `dienthoai`) VALUES
(11, 'A@gmail.com', '1', 'Nguyen', 'Root', '', '', ''),
(13, 'B@gmail.com', '1', 'Nguyen', 'Bao', '25/27 Lê Đức Thọ, P6 Gò Vấp TP. Hồ Chí Minh', '', '0355281276'),
(14, 'phubao9as@gmail.com', '53bd839570b495f7b752184e16b88d54', 'Nguyen', 'Root', '25/27 Lê Đức Thọ, P6 Gò Vấp TP. Hồ Chí Minh', '', '0355281276');

-- --------------------------------------------------------

--
-- Table structure for table `sanpham`
--

CREATE TABLE IF NOT EXISTS `sanpham` (
  `idsp` int(10) unsigned NOT NULL auto_increment,
  `tensp` varchar(100) character set utf8 collate utf8_unicode_ci NOT NULL,
  `gia` int(11) NOT NULL,
  `mota` text character set utf8 NOT NULL,
  `hinh` varchar(50) character set utf8 collate utf8_unicode_ci NOT NULL,
  `giamgia` varchar(100) character set utf8 collate utf8_unicode_ci NOT NULL,
  `idcty` int(10) unsigned NOT NULL, -- Sửa thành unsigned để khớp với congty.idcty
  PRIMARY KEY  (`idsp`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

--
-- Dumping data for table `sanpham`
--

-- --------------------------------------------------------

--
-- Table structure for table `taikhoan`
--

CREATE TABLE IF NOT EXISTS `taikhoan` (
  `iduser` int(10) unsigned NOT NULL auto_increment,
  `username` varchar(50) character set utf8 collate utf8_unicode_ci NOT NULL,
  `password` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL,
  `hodem` varchar(100) character set utf8 collate utf8_unicode_ci NOT NULL,
  `ten` varchar(50) character set utf8 collate utf8_unicode_ci NOT NULL,
  `phanquyen` int(11) NOT NULL default '1',
  `landangnhapcuoi` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`iduser`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=19 ;

--
-- Dumping data for table `taikhoan`
--

INSERT INTO `taikhoan` (`iduser`, `username`, `password`, `hodem`, `ten`, `phanquyen`, `landangnhapcuoi`) VALUES
(11, 'A@gmail.com', '1', 'Nguyen', 'Root', 1, '2025-04-18 11:47:35'),
(12, 'sa', 'sapassword', 'local', 'root', 0, '2025-04-20 09:33:44'),
(13, 'B@gmail.com', '1', 'Nguyen', 'Bao', 1, '2025-04-20 10:29:50'),
(14, 'phubao9as@gmail.com', '53bd839570b495f7b752184e16b88d54', 'Nguyen', 'Root', 0, '2025-04-20 23:17:31'),
(15, 'nhi', 'e5d24a2b53d6d61d9d7f9b9e9d2464d', 'Nhi', 'Nhi', 1, '2025-04-22 20:48:26'),
(16, 'root', 'd41d8cd98f00b204e9800998ecf8427e', 'Root', 'Root', 1, '2025-04-22 22:09:38'),
(17, 'admin', '14a3cfd886be965b3c054eeec15005ca', 'Nguyễn', 'Admin', 0, '2025-04-23 02:03:44'),
(18, 'nhi1', '14a3cfd886be965b3c054eeec15005ca', 'Trần', 'Nhi', 1, '2025-04-23 02:03:44');

-- (Phần còn lại của file giữ nguyên)-- Constraints for dumped tables
--

--
-- Constraints for table `sanpham`
--
ALTER TABLE `sanpham`
  ADD CONSTRAINT `fk_sanpham_congty` FOREIGN KEY (`idcty`) REFERENCES `congty` (`idcty`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `dathang_chitiet`
--
ALTER TABLE `dathang_chitiet`
  ADD CONSTRAINT `fk_iddh` FOREIGN KEY (`iddh`) REFERENCES `dathang` (`iddh`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `khachhang`
--
ALTER TABLE `khachhang`
  ADD CONSTRAINT `fk_idkh` FOREIGN KEY (`idkh`) REFERENCES `taikhoan` (`iduser`) ON DELETE CASCADE ON UPDATE CASCADE;