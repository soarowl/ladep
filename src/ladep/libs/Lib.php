<?php

namespace dekuan\ladep\libs;

use dekuan\delib\CLib;


class Lib
{
	protected static $g_arrStaticRequiredFile	= [];


	static function PrintByCallback( callable $pfnCbFunc, $sType, $sMsg )
	{
		if ( ! is_callable( $pfnCbFunc ) ||
			! CLib::IsExistingString( $sType ) ||
			! CLib::IsExistingString( $sMsg ) )
		{
			return false;
		}

		$pfnCbFunc( $sType, $sMsg );
	}

	static function IsValidUrl( $sUrl )
	{
		return ( is_string( $sUrl ) &&
			strlen( $sUrl ) > 0 &&
			false !== filter_var( $sUrl, FILTER_VALIDATE_URL ) );
	}


	static function TrimPath( $sPath )
	{
		return self::LTrimPath( self::RTrimPath( $sPath ) );
	}
	static function LTrimPath( $sPath )
	{
		if ( ! is_string( $sPath ) )
		{
			return '';
		}
		return ltrim( $sPath, "\r\n\t \\/" );
	}
	static function RTrimPath( $sPath )
	{
		if ( ! is_string( $sPath ) )
		{
			return '';
		}
		return rtrim( $sPath, "\r\n\t \\/" );
	}


	//
	//	for local
	//
	static function GetLocalWorkingRootDir()
	{
		return getcwd();
	}
	static function GetLocalProjectsDir()
	{
		return sprintf
		(
			"%s/%s/",
			self::RTrimPath( self::GetLocalWorkingRootDir() ),
			self::TrimPath( Config::Get( 'dir_projects' ) )
		);
	}
	static function GetLocalProjectsExtensions()
	{
		$arrRet	= [ 'ladep' ];

		//
		//	ext_project	= [ 'ext1', 'ext2' ]
		//
		$arrExts = Config::Get( 'ext_project' );
		if ( is_array( $arrExts ) && count( $arrExts ) )
		{
			$arrRet = [];
			foreach ( $arrExts as $sExt )
			{
				$sExt = strtolower( trim( $sExt ) );
				if ( is_string( $sExt ) &&
					strlen( $sExt ) > 0 )
				{
					$arrRet[] = $sExt;
				}
			}
		}

		return $arrRet;
	}

	static function GetLocalLogsDir()
	{
		return sprintf
		(
			"%s/%s/",
			self::RTrimPath( self::GetLocalWorkingRootDir() ),
			self::TrimPath( Config::Get( 'dir_logs' ) )
		);
	}

	static function GetLocalReleaseDir()
	{
		return sprintf
		(
			"%s/%s/",
			self::RTrimPath( self::GetLocalWorkingRootDir() ),
			self::TrimPath( Config::Get( 'dir_release' ) )
		);
	}
	static function GetLocalReleasedProjectDir( $sProjectName )
	{
		if ( ! is_string( $sProjectName ) || 0 == strlen( $sProjectName ) )
		{
			return '';
		}

		return sprintf
		(
			"%s/%s/",
			self::RTrimPath( self::GetLocalReleaseDir() ),
			self::TrimPath( $sProjectName )
		);
	}
	static function GetLocalReleasedVersionDir( $sProjectName, $sVer )
	{
		if ( ! is_string( $sProjectName ) || 0 == strlen( $sProjectName ) )
		{
			return '';
		}
		if ( ! is_string( $sVer ) || 0 == strlen( $sVer ) )
		{
			return '';
		}

		return sprintf
		(
			"%s/%s/",
			self::RTrimPath( self::GetLocalReleasedProjectDir( $sProjectName ) ),
			self::TrimPath( $sVer )
		);
	}


	//
	//	for phar
	//
	static function GetPharRootDir()
	{
		$sRet		= '';

		//	...
		$sPharRoot = \Phar::running( true );
		if ( is_string( $sPharRoot ) && strlen( $sPharRoot ) > 0 )
		{
			$sRet = sprintf( "%s/src/ladep/", self::RTrimPath( $sPharRoot ) );
		}
		else
		{
			$sRet = dirname( __DIR__ );
		}

		return sprintf( "%s/", self::RTrimPath( $sRet ) );
	}
	static function GetFullPath( $sSubPath )
	{
		if ( ! is_string( $sSubPath ) || 0 == strlen( $sSubPath ) )
		{
			return '';
		}

		//	...
		return sprintf
		(
			"%s/%s",
			self::RTrimPath( self::GetPharRootDir() ),
			self::TrimPath( $sSubPath )
		);
	}
	static function GetRandomString( $nLength = 32 )
	{
		if ( 0 == $nLength )
		{
			return '';
		}

		//	...
		$sRet = '';

		//	...
		$sChars		= '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$nCharsLength	= strlen( $sChars );

		for ( $i = 0; $i < $nLength; $i ++ )
		{
			$sRet .= $sChars[ rand( 0, $nCharsLength - 1 ) ];
		}

		return $sRet;
	}
	static function LoadArray( $sFullFilename )
	{
		if ( ! is_string( $sFullFilename ) || ! is_file( $sFullFilename ) )
		{
			return null;
		}

		//	...
		$arrRet = @ require_once( $sFullFilename );
		if ( is_array( $arrRet ) )
		{
			self::$g_arrStaticRequiredFile[ $sFullFilename ] = $arrRet;
		}
		else if ( is_array( self::$g_arrStaticRequiredFile ) && array_key_exists( $sFullFilename, self::$g_arrStaticRequiredFile ) )
		{
			$arrRet = self::$g_arrStaticRequiredFile[ $sFullFilename ];
		}

		return ( is_array( $arrRet ) ) ? $arrRet : null;
	}

	//
	//	Enumerate directory recursively
	//
	static function REnumerateDir( $sDir, $nLevel = 0 )
	{
		//
		//	sDir		- [in] string	path of directory
		//	nLevel		- [in] int	depth of directory from root directory
		//	RETURN		- array	list of files in sDir and its sub dir
		//
		if ( ! is_string( $sDir ) || ! is_dir( $sDir ) )
		{
			return null;
		}

		//	...
		$arrRet = null;

		try
		{
			$oDir = opendir( $sDir );
			if ( false !== $oDir )
			{
				$arrRet = [];
				while ( false !== ( $sFile = readdir( $oDir ) ) )
				{
					if ( '.' == $sFile || '..' == $sFile )
					{
						continue;
					}

					//	...
					$sFFN = sprintf( "%s/%s", self::RTrimPath( $sDir ), $sFile );
					if ( is_dir( $sFFN ) )
					{
						$arrSubFiles = self::REnumerateDir( $sFFN, ( $nLevel + 1 ) );
						if ( is_array( $arrSubFiles ) && count( $arrSubFiles ) > 0 )
						{
							//
							//	appends all sub files
							//
							foreach ( $arrSubFiles as $sSubFFN )
							{
								if ( is_string( $sSubFFN ) && is_file( $sSubFFN ) )
								{
									$arrRet[] = $sSubFFN;
								}
							}
						}
					}
					else if ( is_file( $sFFN ) )
					{
						$arrRet[] = $sFFN;
					}
				}

				closedir( $oDir );
				$oDir = null;
			}
		}
		catch ( \Exception $e )
		{
			throw $e;
		}

		//	...
		return $arrRet;
	}

	//
	//	Remove directory recursively
	//
	static function RRmDir( $sDir, $bRetainRoot = false, $nLevel = 0 )
	{
		//
		//	sDir		- path of directory
		//	bRetainRoot	- retain or remove root directory
		//	nLevel		- depth of directory from root directory
		//	RETURN		- true / false
		//
		if ( ! is_string( $sDir ) || ! is_dir( $sDir ) )
		{
			return false;
		}

		//	...
		$bRet = false;

		try
		{
			$oDir = opendir( $sDir );
			if ( false !== $oDir )
			{
				while( false !== ( $sFile = readdir( $oDir ) ) )
				{
					if ( '.' == $sFile || '..' == $sFile )
					{
						continue;
					}

					//	...
					$sFFN = sprintf( "%s/%s", self::RTrimPath( $sDir ), $sFile );
					if ( is_dir( $sFFN ) )
					{
						self::RRmDir( $sFFN, $bRetainRoot, ( $nLevel + 1 ) );
					}
					else
					{
						unlink( $sFFN );
					}
				}

				closedir( $oDir );
				$oDir = null;
			}

			if ( $bRetainRoot && 0 == $nLevel )
			{
				$bRet = true;
			}
			else
			{
				$bRet = @ rmdir( $sDir );
			}
		}
		catch ( \Exception $e )
		{
			throw $e;
		}

		//	...
		return $bRet;
	}
	static function RRmDirByName( $sDir, $bRetainRoot = false, $sDirName = '', $nLevel = 0, callable $pfnCbFunc = null )
	{
		//
		//	sDir		- path of directory
		//	bRetainRoot	- retain or remove root directory
		//	sDirName	- the name of directory
		//	nLevel		- depth of directory from root directory
		//	RETURN		- true / false
		//
		if ( ! is_string( $sDir ) || ! is_dir( $sDir ) )
		{
			return false;
		}

		//	...
		$bRet = false;

		try
		{
			$oDir = opendir( $sDir );
			if ( false !== $oDir )
			{
				while( false !== ( $sFile = readdir( $oDir ) ) )
				{
					if ( '.' == $sFile || '..' == $sFile )
					{
						continue;
					}

					//	...
					$sFFN = sprintf( "%s/%s", self::RTrimPath( $sDir ), $sFile );
					if ( is_dir( $sFFN ) )
					{
						if ( 0 == strcasecmp( $sFile, $sDirName ) )
						{
							if ( is_callable( $pfnCbFunc ) )
							{
								$pfnCbFunc( true, $sFFN );
							}
							self::RRmDir( $sFFN, $bRetainRoot, 0 );
						}
						else
						{
							self::RRmDirByName( $sFFN, $bRetainRoot, $sDirName, ( $nLevel + 1 ), $pfnCbFunc );
						}
					}
				}

				closedir( $oDir );
				$oDir = null;
			}

			$bRet = true;

		//	if ( $bRetainRoot && 0 == $nLevel )
		//	{
		//		$bRet = true;
		//	}
		//	else
		//	{
		//		$bRet = @ rmdir( $sDir );
		//	}
		}
		catch ( \Exception $e )
		{
			throw $e;
		}

		//	...
		return $bRet;
	}
	static function RRmFileByName( $sDir, $bRetainRoot = false, $sFileName = '', $nLevel = 0, callable $pfnCbFunc = null )
	{
		//
		//	sDir		- path of directory
		//	bRetainRoot	- retain or remove root directory
		//	sFileName	- file name
		//	nLevel		- depth of directory from root directory
		//	RETURN		- true / false
		//
		if ( ! is_string( $sDir ) || ! is_dir( $sDir ) )
		{
			return false;
		}

		//	...
		$bRet = false;

		try
		{
			$oDir = opendir( $sDir );
			if ( false !== $oDir )
			{
				while( false !== ( $sFile = readdir( $oDir ) ) )
				{
					if ( '.' == $sFile || '..' == $sFile )
					{
						continue;
					}

					//	...
					$sFFN = sprintf( "%s/%s", self::RTrimPath( $sDir ), $sFile );
					if ( is_dir( $sFFN ) )
					{
						self::RRmFileByName( $sFFN, $bRetainRoot, $sFileName, ( $nLevel + 1 ), $pfnCbFunc );
					}
					else
					{
						if ( 0 == strcasecmp( $sFile, $sFileName ) )
						{
							if ( is_callable( $pfnCbFunc ) )
							{
								$pfnCbFunc( true, $sFFN );
							}
							@ unlink( $sFFN );
						}
					}
				}

				closedir( $oDir );
				$oDir = null;
			}

			$bRet = true;

		//	if ( $bRetainRoot && 0 == $nLevel )
		//	{
		//		$bRet = true;
		//	}
		//	else
		//	{
		//		$bRet = @ rmdir( $sDir );
		//	}

		}
		catch ( \Exception $e )
		{
			throw $e;
		}

		//	...
		return $bRet;
	}
}