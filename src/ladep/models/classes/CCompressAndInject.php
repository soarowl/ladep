<?php

namespace dekuan\ladep\models\classes;

use dekuan\vdata\CConst;
use dekuan\ladep\libs;
use dekuan\ladep\libs\Config;
use dekuan\ladep\models\compressores;


class CCompressAndInject
{
	public function CompressAllViews( $sProjectName, $sVer, $arrOptions, callable $pfnCbFunc )
	{
		//
		//	sProjectName
		//	sVer
		//	arrOptions	- [ 'no-compress-js', 'no-compress-css' ]
		//	pfnCbFunc
		//
		if ( ! is_callable( $pfnCbFunc ) )
		{
			return false;
		}
		if ( ! is_string( $sProjectName ) || 0 == strlen( $sProjectName ) )
		{
			libs\Lib::PrintByCallback( $pfnCbFunc, "comment", "# invalid parameter sProjectName : " . __FUNCTION__ );
			return false;
		}
		if ( ! is_string( $sVer ) || 0 == strlen( $sVer ) )
		{
			libs\Lib::PrintByCallback( $pfnCbFunc, "comment", "# invalid parameter sVer : " . __FUNCTION__ );
			return false;
		}

		//	...
		$bRet = false;

		//
		//	...
		//
		$sViewDir	= sprintf
		(
			"%s/%s",
			libs\Lib::RTrimPath( libs\Lib::GetLocalReleasedVersionDir( $sProjectName, $sVer ) ),
			libs\Lib::LTrimPath( Config::Get( 'dir_la_resources_views' ) )
		);
		$sPublicDir	= sprintf
		(
			"%s/%s",
			libs\Lib::RTrimPath( libs\Lib::GetLocalReleasedVersionDir( $sProjectName, $sVer ) ),
			libs\Lib::LTrimPath( Config::Get( 'dir_la_public' ) )
		);

		if ( is_string( $sViewDir ) && is_dir( $sViewDir ) )
		{
			if ( is_string( $sPublicDir ) && is_dir( $sPublicDir ) )
			{
				$arrFiles = libs\Lib::REnumerateDir( $sViewDir );
				if ( is_array( $arrFiles ) && count( $arrFiles ) > 0 )
				{
					$pfnCbFunc( 'info', count( $arrFiles ) . " view files was found:" );
					foreach ( $arrFiles as $nIndex => $sFullFilename )
					{
						$pfnCbFunc( 'info', sprintf( "\t\t  %02d - %s", $nIndex, $sFullFilename ) );
					}

					//	...
					$bRet = true;

					//	...
					$pfnCbFunc( 'info', "" );
					foreach ( $arrFiles as $sViewFFN )
					{
						$bRet &= $this->_CreateCompressedView
						(
							$sProjectName,
							$sVer,
							$sViewFFN,
							$sPublicDir,
							$arrOptions,
							$pfnCbFunc
						);
					}
				}
			}
			else
			{
				libs\Lib::PrintByCallback( $pfnCbFunc, 'comment', "# invalid web public dir" );
			}
		}
		else
		{
			libs\Lib::PrintByCallback( $pfnCbFunc, 'comment', "# invalid web view dir" );
		}

		return $bRet;
	}


	private function _CreateCompressedView( $sProjectName, $sVer, $sViewFullFilename, $sWebRootDir, $arrOptions, callable $pfnCbFunc )
	{
		if ( ! is_callable( $pfnCbFunc ) )
		{
			return false;
		}
		if ( ! is_string( $sProjectName ) || 0 == strlen( $sProjectName ) )
		{
			libs\Lib::PrintByCallback( $pfnCbFunc, 'comment', "# invalid parameter sProjectName" );
			return false;
		}
		if ( ! is_string( $sVer ) || 0 == strlen( $sVer ) )
		{
			libs\Lib::PrintByCallback( $pfnCbFunc, 'comment', "# invalid parameter sVer" );
			return false;
		}
		if ( ! is_string( $sViewFullFilename ) || ! is_file( $sViewFullFilename ) )
		{
			libs\Lib::PrintByCallback( $pfnCbFunc, 'comment', "# invalid parameter sViewFullFilename" );
			return false;
		}
		if ( ! is_string( $sWebRootDir ) || ! is_dir( $sWebRootDir ) )
		{
			libs\Lib::PrintByCallback( $pfnCbFunc, 'comment', "# invalid parameter sWebRootDir" );
			return false;
		}


		//	...
		$cCompressor	= new compressores\CMakeCompressed();
		$arrMakeReturn	= [];
		$nCompressed	= $cCompressor->MakeCompressedView
					(
						$sProjectName,
						$sVer,
						$sViewFullFilename,
						$sWebRootDir,
						false,
						$arrOptions,
						$arrMakeReturn,
						$pfnCbFunc
					);

		//	...
		if ( CConst::ERROR_SUCCESS == $nCompressed )
		{
			if ( is_array( $arrMakeReturn ) )
			{
				if ( array_key_exists( 'js', $arrMakeReturn ) )
				{
					if ( array_key_exists( 'all_in_one_ffn', $arrMakeReturn['js'] ) &&
						is_string( $arrMakeReturn['js']['all_in_one_ffn'] ) &&
						is_file( $arrMakeReturn['js']['all_in_one_ffn'] ) )
					{
						if ( array_key_exists( 'compressed_ffn', $arrMakeReturn['js'] ) &&
							is_string( $arrMakeReturn['js']['compressed_ffn'] ) &&
							is_file( $arrMakeReturn['js']['compressed_ffn'] ))
						{
							//	@unlink( $arrMakeReturn['js']['compressed_ffn'] );
						}

						//	@unlink( $arrMakeReturn['js']['all_in_one_ffn'] );
					}
				}

				if ( array_key_exists( 'css', $arrMakeReturn ) )
				{
					if ( array_key_exists( 'all_in_one_ffn', $arrMakeReturn['css'] ) &&
						is_string( $arrMakeReturn['css']['all_in_one_ffn'] ) &&
						is_file( $arrMakeReturn['css']['all_in_one_ffn'] ) )
					{
						if ( array_key_exists( 'compressed_ffn', $arrMakeReturn['css'] ) &&
							is_string( $arrMakeReturn['css']['compressed_ffn'] ) &&
							is_file( $arrMakeReturn['css']['compressed_ffn'] ))
						{
							//	@unlink( $arrMakeReturn['css']['compressed_ffn'] );
						}

						//	@unlink( $arrMakeReturn['css']['all_in_one_ffn'] );
					}
				}
			}
		}

		return true;
	}
}