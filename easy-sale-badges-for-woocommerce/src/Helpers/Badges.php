<?php

namespace AsanaPlugins\WooCommerce\SaleBadges\Helpers\Badges;

defined( 'ABSPATH' ) || exit;

use function AsanaPlugins\WooCommerce\SaleBadges\add_custom_style;
use function AsanaPlugins\WooCommerce\SaleBadges\is_pro_active;
use function AsanaPlugins\WooCommerce\SaleBadges\translate;

function output_badges( $product, $badges, $hide = false, $return = false, $out_of_image = false ) {
	if ( empty( $badges ) ) {
		return '';
	}

	$output = '';
	foreach ( $badges as $badge ) {
		$out = output_badge( $product, $badge, $hide, $return, $out_of_image );
		if ( ! empty( $out ) ) {
			$output .= $out;
		}
	}
	return $output;
}

function output_badge( $product, $badge, $hide = false, $return = false, $out_of_image = false ) {

	if ( $hide ) {
    	if ( isset( $badge->showOnProductPage ) && 0 === $badge->showOnProductPage ) {
        	if ( $return ) {
            	return '';
        	}
        	return;
    	}
	} elseif ( isset( $badge->showOnArchivePage ) && 0 === $badge->showOnArchivePage ) {
    	if ( $return ) {
        	return '';
    	}
    	return;
	}

	if ( isset( $badge->imgbadge ) && $badge->imgbadge == 1 ) {
		if ( is_pro_active() ) {
			return \AsanaPlugins\WooCommerce\SaleBadgesPro\Helpers\Badges\output_image_badge( $product, $badge, $hide, $return, $out_of_image );
		}
	} elseif ( isset( $badge->imgbadgeAdv ) && $badge->imgbadgeAdv == 1 ) {
		if ( is_pro_active() ) {
			return \AsanaPlugins\WooCommerce\SaleBadgesPro\Helpers\Badges\output_image_adv_badge( $product, $badge, $hide, $return, $out_of_image );
		}
	} elseif ( isset( $badge->useTimerBadge ) && $badge->useTimerBadge == 1 ) {
		if ( is_pro_active() ) {
			return \AsanaPlugins\WooCommerce\SaleBadgesPro\Helpers\Badges\output_timer_badge( $product, $badge, $hide, $return, $out_of_image );
		}
	} elseif ( ! empty( $badge->badgeStyles ) ) {
		return output_css_badge( $product, $badge, $hide, $return, $out_of_image );
	}

	if ( $return ) {
		return '';
	}
}

function output_css_badge( $product, $badge, $hide = false, $return = false, $out_of_image = false ) {
	if ( ! $badge ) {
		return '';
	}

	if ( null !== $out_of_image && ! empty( $badge->cssLabelPosition ) ) {
		if ( $out_of_image && 'onImage' === $badge->cssLabelPosition ) {
			return '';
		}

		if ( ! $out_of_image && 'outOfImage' === $badge->cssLabelPosition ) {
			return '';
		}
	} elseif ( $out_of_image ) {
		return '';
	}

	// If threshold is set, check the threshold time is reached.
	if ( isset( $badge->selectedDateFrom ) && '' != $badge->selectedDateFrom ) {
		$now = current_time( 'timestamp' );
		if ( 0 > $now - strtotime( $badge->selectedDateFrom, $now ) ) {
			return '';
		}
	}

	$class_names = 'asnp-esb-badge-element asnp-esb-productBadge asnp-esb-productBadge-'. absint( $badge->id );

	if ( ! empty( $badge->cssLabelPosition ) && 'outOfImage' === $badge->cssLabelPosition ) {
		$class_names .= ' asnp-esb-css-label-out-of-image asnp-position-css-label';
		$hide = false;
	  } else {
		$class_names .= ' asnp-esb-css-label-on-image';
	  }

	if ( $hide ) {
		$class_names .= ' asnp-esb-badge-hidden';
	}

	$dynamic_styles = css_badge_dynamic_styles( $badge, $hide, $out_of_image );

	add_custom_style( $dynamic_styles, $badge );

	$class_names = apply_filters( 'asnp_wesb_css_badge_class_names', $class_names, $badge, $hide );

	$label = translate( $badge->badgeLabel, 'labelTranslate', $badge );
	$label = apply_filters( 'asnp_wesb_css_badge_label', $label, $badge, $product );

	add_filter( 'safe_style_css', 'AsanaPlugins\WooCommerce\SaleBadges\allowed_inline_styles' );

	// Css Badge
	$output = '<div class="' . esc_attr( $class_names ) . '"' . ( $hide ? ' style="display: none;"' : '' ) . '>';
	$output .= '<div class="asnp-esb-badge-'. absint( $badge->id ) .'">';
	$output .= '<span class="asnp-esb-inner-span2-'. absint( $badge->id ) .'"></span>';
	$output .= '<div class="asnp-esb-inner-span1-'. absint( $badge->id ) .'">';
	$output .= '<div class="asnp-esb-inner-span4-'. absint( $badge->id ) .'">' . wp_kses_post( $label ) . '</div>';
	$output .= '</div>';
	$output .= '</div>';
	$output .= '</div>';

	$output = apply_filters( 'asnp_wesb_css_badge', $output, $badge, $hide, $product );
	$output = wp_kses_post( $output );

	remove_filter( 'safe_style_css', 'AsanaPlugins\WooCommerce\SaleBadges\allowed_inline_styles' );

	if ( $return ) {
		return $output;
	}

	echo $output;
}

function css_badge_dynamic_styles( $badge, $hide = false, $out_of_image = false  ) {
	if ( ! $badge ) {
		return '';
	}

	if ( null !== $out_of_image && ! empty( $badge->cssLabelPosition ) ) {
		if ( $out_of_image && 'onImage' === $badge->cssLabelPosition ) {
			return '';
		}

		if ( ! $out_of_image && 'outOfImage' === $badge->cssLabelPosition ) {
			return '';
		}
	} elseif ( $out_of_image ) {
		return '';
	}

	$inset_property = '';
	if ( ! empty( $badge->badgePositionX ) && ! empty( $badge->badgePositionY ) ) {
		if ( 'top' === $badge->badgePositionY ) {
			if ( isset( $badge->badgeStyles ) && 'badge11' === $badge->badgeStyles ) {
				if ( isset( $badge->badgePositionTop ) ) {
					$inset_property = $badge->badgePositionTop . 'px auto auto 0px';
				}
			} elseif ( 'left' === $badge->badgePositionX ) {
				if ( isset( $badge->badgePositionTop ) && isset( $badge->badgePositionLeft ) ) {
					$inset_property = $badge->badgePositionTop . 'px auto auto ' . $badge->badgePositionLeft . 'px';
				}
			} elseif ( 'right' === $badge->badgePositionX ) {
				if ( isset( $badge->badgePositionTop ) && isset( $badge->badgePositionRight ) ) {
					$inset_property = $badge->badgePositionTop . 'px ' . $badge->badgePositionRight . 'px auto auto';
				}
			} elseif ( 'center' === $badge->badgePositionX ) {
				if ( isset( $badge->badgePositionTop ) ) {
					$inset_property = $badge->badgePositionTop . 'px auto auto 55px';
				}
			}
		} elseif ( 'bottom' === $badge->badgePositionY ) {
			if ( isset( $badge->badgeStyles ) && 'badge11' === $badge->badgeStyles ) {
				if ( isset( $badge->badgePositionBottom ) ) {
					$inset_property = 'auto 0px ' . $badge->badgePositionBottom . 'px auto';
				}
			} elseif ( 'left' === $badge->badgePositionX ) {
				if ( isset( $badge->badgePositionBottom ) && isset( $badge->badgePositionLeft ) ) {
					$inset_property = 'auto auto ' . $badge->badgePositionBottom . 'px ' . $badge->badgePositionLeft . 'px';
				}
			} elseif ( 'right' === $badge->badgePositionX ) {
				if ( isset( $badge->badgePositionBottom ) && isset( $badge->badgePositionRight ) ) {
					$inset_property = 'auto ' . $badge->badgePositionRight . 'px ' . $badge->badgePositionBottom . 'px auto';
				}
			} elseif ( 'center' === $badge->badgePositionX ) {
				if ( isset( $badge->badgePositionBottom ) ) {
					$inset_property = 'auto auto ' . $badge->badgePositionBottom . 'px 55px';
				}
			}
		}
 	}

	$height_cont_badge = '';
	$width_cont_badge = '';

	if ( isset( $badge->badgeStyles ) && $badge->badgeStyles == 'badge11' ) {
		$width_cont_badge = '100%';
	} elseif ( isset( $badge->widthBadge ) ) {
		$width_cont_badge = $badge->widthBadge . 'px';
	}

	if (
		isset( $badge->widthBadge ) &&
		isset( $badge->badgeStyles ) &&
		(
			$badge->badgeStyles == 'badge5'  ||
			$badge->badgeStyles == 'badge6'  ||
			$badge->badgeStyles == 'badge7'  ||
			$badge->badgeStyles == 'badge8'  ||
			$badge->badgeStyles == 'badge14' ||
			$badge->badgeStyles == 'badge15' ||
			$badge->badgeStyles == 'badge16' ||
			$badge->badgeStyles == 'badge17' ||
			$badge->badgeStyles == 'badge18'
		)
	) {
		$height_cont_badge = $badge->widthBadge . 'px';
	} elseif ( isset( $badge->widthBadge ) && isset( $badge->badgeStyles ) && (
			$badge->badgeStyles == 'badge9' ||
			$badge->badgeStyles == 'badge10' )
		) {
		$height_cont_badge = $badge->heightBadge - 15 . 'px';
	} elseif ( isset( $badge->heightBadge ) ) {
		$height_cont_badge = $badge->heightBadge . 'px';
	}

	$horiz_and_vert = '';
	if ( ! empty( $badge->horizontal ) ) {
		if ( ! empty( $badge->vertical ) ) {
			$horiz_and_vert = 'scaleX(-1) scaleY(-1)';
		} else {
			$horiz_and_vert = 'scaleX(-1)';
		}
	} elseif ( ! empty( $badge->vertical ) ) {
		$horiz_and_vert = 'scaleY(-1)';
	}

	if (
		isset( $badge->badgeStyles ) && $badge->badgeStyles == 'badge5' &&
		isset( $badge->badgePositionY ) && $badge->badgePositionY == 'bottom' &&
		(
			( isset( $badge->badgePositionX ) && $badge->badgePositionX == 'left' ) ||
			( isset( $badge->badgePositionX ) && $badge->badgePositionX == 'right' )
		)
	) {
		$horiz_and_vert = 'scaleX(-1) scaleY(-1)';
	} elseif (
		isset( $badge->badgeStyles ) && $badge->badgeStyles == 'badge6' &&
		isset( $badge->badgePositionY ) && $badge->badgePositionY == 'bottom' &&
		(
			( isset( $badge->badgePositionX ) && $badge->badgePositionX == 'left' ) ||
			( isset( $badge->badgePositionX ) && $badge->badgePositionX == 'right' )
		)
	) {
		$horiz_and_vert = 'scaleX(-1) scaleY(-1)';
	}

	$nowrap = isset( $badge->badgeStyles ) && 'badge13' === $badge->badgeStyles ? 'normal' : 'nowrap';

	$dynamic_styles = '.asnp-esb-inner-span4-'. absint( $badge->id ) .' {';
	$dynamic_styles .= ' white-space: ' . esc_html( $nowrap ) . ';';
	if ( ! empty( $horiz_and_vert ) ) {
		$dynamic_styles .= ' transform: ' . esc_html( $horiz_and_vert ) . ';';
	}
	$dynamic_styles .= '}';

	$styles = '';

	if ( isset( $badge->animationSelect ) ) {
		$styles .= ' animation-name: ' . esc_html( $badge->animationSelect ) . ';';
	}
	if ( isset( $badge->animateDuration ) ) {
		$styles .= ' animation-duration: ' . esc_html( $badge->animateDuration ) . 's;';
	}
	if ( isset( $badge->animationCount ) ) {
		$styles .= ' animation-iteration-count: ' . esc_html( $badge->animationCount ) . ';';
	}
	if ( isset( $badge->boxShadowWidth ) && isset( $badge->badgeColorShadow ) ) {
		$styles .= ' filter: drop-shadow(' . esc_html( $badge->badgeColorShadow ) . ' ' . esc_html( $badge->boxShadowWidth ) . 'px ' . esc_html( $badge->boxShadowWidth ) . 'px ' . esc_html( $badge->boxShadowWidth ) . 'px);';
	}

	if ( empty( $badge->cssLabelPosition ) || 'onImage' === $badge->cssLabelPosition ) {
		$styles .= ' position: absolute;';
	}

	if ( ! empty( $styles ) ) {
		$dynamic_styles .= '.asnp-esb-badge-'. absint( $badge->id ) . ' {' . $styles . '}';
	}

	if ( isset( $badge->cssLabelPosition ) && 'outOfImage' == $badge->cssLabelPosition) {
		$dynamic_styles .= '.asnp-position-css-label {';
		if ( isset( $badge->badgePositionOutofImage ) ) {
			$dynamic_styles .= ' justify-content: ' . $badge->badgePositionOutofImage . ';';
			$dynamic_styles .= ' display: flex;';
			$dynamic_styles .= ' width: 100% !important;';
		}
		$dynamic_styles .= '}';
	}

	switch ( $badge->badgeStyles ) {
		case 'badge1':
			$dynamic_styles .= '.asnp-esb-productBadge-'. absint( $badge->id ) .' {';
			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $width_cont_badge . ';';
			}
			if ( isset( $badge->heightBadge ) ) {
				$dynamic_styles .= ' height: ' . $height_cont_badge . ';';
			}
			if ( isset( $badge->badgePositionTop ) ) {
				$dynamic_styles .= ' inset: ' . $inset_property . ';';
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.asnp-esb-badge-'. absint( $badge->id ) .' {';
			$dynamic_styles .= ' text-align: center;';
			$dynamic_styles .= ' text-shadow: none;';

			if ( isset( $badge->badgeColor ) ) {
				$dynamic_styles .= ' background: ' . $badge->badgeColor . ';';
			}
			if ( isset( $badge->heightBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->heightBadge . 'px;';
			}
			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->widthBadge . 'px;';
			}
			if ( isset( $badge->badgePositionX ) ) {
				$dynamic_styles .= ' left: ' . ( $badge->badgePositionX == 'right' ? 'auto' : '0px' ) . ';';
			}
			if ( isset( $badge->badgePositionX ) && 'right' === $badge->badgePositionX ) {
				$dynamic_styles .= ' right: 0px;';
			}
			if ( isset( $badge->textColor ) ) {
				$dynamic_styles .= ' color: ' . $badge->textColor . ';';
			}
			if ( isset( $badge->fontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->fontSizeText . 'px;';
			}
			if ( isset( $badge->fontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->fontWeightLabel . ';';
			}
			if ( isset( $badge->lineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->lineHeightText . 'px;';
			}
			if ( isset( $badge->opacity ) ) {
				$dynamic_styles .= ' opacity: ' . $badge->opacity . ';';
			}
			if ( isset( $badge->topLeftRadius ) ) {
				$dynamic_styles .= ' border-top-left-radius: ' . $badge->topLeftRadius . 'px;';
			}
			if ( isset( $badge->topRightRadius ) ) {
				$dynamic_styles .= ' border-top-right-radius: ' . $badge->topRightRadius . 'px;';
			}
			if ( isset( $badge->bottomLeftRadius ) ) {
				$dynamic_styles .= ' border-bottom-left-radius: ' . $badge->bottomLeftRadius . 'px;';
			}
			if ( isset( $badge->bottomRightRadius ) ) {
				$dynamic_styles .= ' border-bottom-right-radius: ' . $badge->bottomRightRadius . 'px;';
			}
			if ( isset( $badge->zIndex ) ) {
				$dynamic_styles .= ' z-index: ' . $badge->zIndex . ';';
			}

			$transform = '';
			if ( isset( $badge->rotationX ) ) {
				$transform .= ' rotateX(' . esc_html( $badge->rotationX ) . 'deg)';
			}
			if ( isset( $badge->rotationY ) ) {
				$transform .= ' rotateY(' . esc_html( $badge->rotationY ) . 'deg) ';
			}
			if ( isset( $badge->rotationZ ) ) {
				$transform .= ' rotateZ(' . esc_html( $badge->rotationZ ) . 'deg);';
			}
			if ( ! empty( $transform ) ) {
				$dynamic_styles .= ' transform:' . $transform;
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.single .asnp-esb-badge-'. absint( $badge->id ) .' {';
			if ( isset( $badge->singleHeightBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->singleHeightBadge . 'px;';
			} elseif ( isset( $badge->heightBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->heightBadge  . 'px;';
			}
			if ( isset( $badge->singleWidthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->singleWidthBadge  . 'px;';
			} elseif ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->widthBadge  . 'px;';
			}
			if ( isset( $badge->singleFontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->singleFontSizeText . 'px;';
			} elseif ( isset( $badge->fontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->fontSizeText  . 'px;';
			}
			if ( isset( $badge->singleFontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->singleFontWeightLabel . ';';
			} elseif ( isset( $badge->fontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->fontWeightLabel  . ';';
			}
			if ( isset( $badge->singleLineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->singleLineHeightText . 'px;';
			} elseif ( isset( $badge->lineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->lineHeightText  . 'px;';
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.related .asnp-esb-badge-'. absint( $badge->id ) .' {';
			if ( isset( $badge->heightBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->heightBadge . 'px;';
			}
			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->widthBadge . 'px;';
			}
			if ( isset( $badge->fontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->fontSizeText . 'px;';
			}
			if ( isset( $badge->fontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->fontWeightLabel . ';';
			}
			if ( isset( $badge->lineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->lineHeightText . 'px;';
			}
			$dynamic_styles .= '}';
			break;

		case 'badge2':
			$dynamic_styles .= '.asnp-esb-productBadge-'. absint( $badge->id ) .' {';
			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $width_cont_badge . ';';
			}
			if ( isset( $badge->heightBadge ) ) {
				$dynamic_styles .= ' height: ' . $height_cont_badge . ';';
			}
			if ( isset( $badge->badgePositionTop ) ) {
				$dynamic_styles .= ' inset: ' . $inset_property . ';';
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.asnp-esb-badge-'. absint( $badge->id ) .' {';
			$dynamic_styles .= ' text-align: center;';
			$dynamic_styles .= ' text-shadow: none;';

			if ( isset( $badge->badgeColor ) ) {
				$dynamic_styles .= ' background: ' . $badge->badgeColor . ';';
			}
			if ( isset( $badge->heightBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->heightBadge . 'px;';
			}
			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->widthBadge . 'px;';
			}
			if ( isset( $badge->badgePositionX ) ) {
				$dynamic_styles .= ' left: ' . ( $badge->badgePositionX == 'right' ? 'auto' : '0px' ) . ';';
			}
			if ( isset( $badge->badgePositionX ) && 'right' === $badge->badgePositionX ) {
				$dynamic_styles .= ' right: 0px;';
			}
			if ( isset( $badge->fontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->fontSizeText . 'px;';
			}
			if ( isset( $badge->fontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->fontWeightLabel . ';';
			}
			if ( isset( $badge->textColor ) ) {
				$dynamic_styles .= ' color: ' . $badge->textColor . ';';
			}
			if ( isset( $badge->lineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->lineHeightText . 'px;';
			}
			if ( isset( $badge->opacity ) ) {
				$dynamic_styles .= ' opacity: ' . $badge->opacity . ';';
			}
			if ( isset( $badge->topLeftRadius ) ) {
				$dynamic_styles .= ' border-top-left-radius: ' . $badge->topLeftRadius . 'px;';
			}
			if ( isset( $badge->topRightRadius ) ) {
				$dynamic_styles .= ' border-top-right-radius: ' . $badge->topRightRadius . 'px;';
			}
			if ( isset( $badge->bottomLeftRadius ) ) {
				$dynamic_styles .= ' border-bottom-left-radius: ' . $badge->bottomLeftRadius . 'px;';
			}
			if ( isset( $badge->bottomRightRadius ) ) {
				$dynamic_styles .= ' border-bottom-right-radius: ' . $badge->bottomRightRadius . 'px;';
			}
			if ( isset( $badge->zIndex ) ) {
				$dynamic_styles .= ' z-index: ' . $badge->zIndex . ';';
			}

			$transform = '';
			if ( isset( $badge->rotationX ) ) {
				$transform .= ' rotateX(' . esc_html( $badge->rotationX ) . 'deg)';
			}
			if ( isset( $badge->rotationY ) ) {
				$transform .= ' rotateY(' . esc_html( $badge->rotationY ) . 'deg) ';
			}
			if ( isset( $badge->rotationZ ) ) {
				$transform .= ' rotateZ(' . esc_html( $badge->rotationZ ) . 'deg);';
			}
			if ( ! empty( $transform ) ) {
				$dynamic_styles .= ' transform:' . $transform;
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.single .asnp-esb-badge-'. absint( $badge->id ) .' {';
			if ( isset( $badge->singleHeightBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->singleHeightBadge . 'px;';
			} elseif ( isset( $badge->heightBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->heightBadge  . 'px;';
			}
			if ( isset( $badge->singleWidthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->singleWidthBadge  . 'px;';
			} elseif ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->widthBadge  . 'px;';
			}
			if ( isset( $badge->singleFontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->singleFontSizeText . 'px;';
			} elseif ( isset( $badge->fontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->fontSizeText  . 'px;';
			}
			if ( isset( $badge->singleFontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->singleFontWeightLabel . ';';
			} elseif ( isset( $badge->fontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->fontWeightLabel  . ';';
			}
			if ( isset( $badge->singleLineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->singleLineHeightText . 'px;';
			} elseif ( isset( $badge->lineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->lineHeightText  . 'px;';
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.related .asnp-esb-badge-'. absint( $badge->id ) .' {';
			if ( isset( $badge->heightBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->heightBadge . 'px;';
			}
			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->widthBadge . 'px;';
			}
			if ( isset( $badge->fontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->fontSizeText . 'px;';
			}
			if ( isset( $badge->fontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->fontWeightLabel . ';';
			}
			if ( isset( $badge->lineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->lineHeightText . 'px;';
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.asnp-esb-badge-'. absint( $badge->id ) .'::before {';
			$dynamic_styles .= ' border-left-color: transparent !important;';
			$dynamic_styles .= ' display: inline-block;';
			$dynamic_styles .= ' content: \'\';';
			$dynamic_styles .= ' position: absolute;';

			if ( isset( $badge->badgePositionX ) ) {
				$dynamic_styles .= ' right: ' . ( $badge->badgePositionX == 'right' ? 'auto' : '-20px' ) . ';';
				$dynamic_styles .= ' left: ' . ( $badge->badgePositionX == 'right' ? '-20px' : '' ) . ';';
			}

			$dynamic_styles .= ' top: 0;';
			$dynamic_styles .= ' border: 9px solid transparent;';
			$dynamic_styles .= ' border-width: 15px 15px;';

			if ( isset( $badge->badgeColor ) ) {
				$dynamic_styles .= ' border-color: ' . $badge->badgeColor . ';';
			}

			$dynamic_styles .= ' transform: ' . ( isset( $badge->badgePositionX ) && $badge->badgePositionX == 'right' ? 'rotate(0)' : 'rotate(180deg)' ) . ';';
			$dynamic_styles .= '}';
		break;

		case 'badge3':
			$dynamic_styles .= '.asnp-esb-productBadge-'. absint( $badge->id ) .' {';
			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $width_cont_badge . ';';
			}
			if ( isset( $badge->heightBadge ) ) {
				$dynamic_styles .= ' height: ' . $height_cont_badge . ';';
			}
			if ( isset( $badge->badgePositionTop ) ) {
				$dynamic_styles .= ' inset: ' . $inset_property . ';';
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.asnp-esb-badge-'. absint( $badge->id ) .' {';
			$dynamic_styles .= ' text-align: center;';
			$dynamic_styles .= ' text-shadow: none;';
			if ( isset( $badge->badgeColor ) ) {
				$dynamic_styles .= ' background: ' . $badge->badgeColor . ';';
			}
			if ( isset( $badge->heightBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->heightBadge . 'px;';
			}
			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->widthBadge . 'px;';
			}
			if ( isset( $badge->badgePositionX ) ) {
				$dynamic_styles .= ' left: ' . ( $badge->badgePositionX == 'right' ? 'auto' : '0px' ) . ';';
			}
			if ( isset( $badge->badgePositionX ) && 'right' === $badge->badgePositionX ) {
				$dynamic_styles .= ' right: 0px;';
			}
			if ( isset( $badge->fontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->fontSizeText . 'px;';
			}
			if ( isset( $badge->fontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->fontWeightLabel . ';';
			}
			if ( isset( $badge->textColor ) ) {
				$dynamic_styles .= ' color: ' . $badge->textColor . ';';
			}
			if ( isset( $badge->lineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->lineHeightText . 'px;';
			}
			if ( isset( $badge->opacity ) ) {
				$dynamic_styles .= ' opacity: ' . $badge->opacity . ';';
			}
			if ( isset( $badge->topLeftRadius ) ) {
				$dynamic_styles .= ' border-top-left-radius: ' . $badge->topLeftRadius . 'px;';
			}
			if ( isset( $badge->topRightRadius ) ) {
				$dynamic_styles .= ' border-top-right-radius: ' . $badge->topRightRadius . 'px;';
			}
			if ( isset( $badge->bottomLeftRadius ) ) {
				$dynamic_styles .= ' border-bottom-left-radius: ' . $badge->bottomLeftRadius . 'px;';
			}
			if ( isset( $badge->bottomRightRadius ) ) {
				$dynamic_styles .= ' border-bottom-right-radius: ' . $badge->bottomRightRadius . 'px;';
			}
			if ( isset( $badge->zIndex ) ) {
				$dynamic_styles .= ' z-index: ' . $badge->zIndex . ';';
			}

			$transform = '';
			if ( isset( $badge->rotationX ) ) {
				$transform .= ' rotateX(' . esc_html( $badge->rotationX ) . 'deg)';
			}
			if ( isset( $badge->rotationY ) ) {
				$transform .= ' rotateY(' . esc_html( $badge->rotationY ) . 'deg) ';
			}
			if ( isset( $badge->rotationZ ) ) {
				$transform .= ' rotateZ(' . esc_html( $badge->rotationZ ) . 'deg);';
			}
			if ( ! empty( $transform ) ) {
				$dynamic_styles .= ' transform:' . $transform;
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.single .asnp-esb-badge-'. absint( $badge->id ) .' {';
			if ( isset( $badge->singleHeightBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->singleHeightBadge . 'px;';
			} elseif ( isset( $badge->heightBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->heightBadge  . 'px;';
			}
			if ( isset( $badge->singleWidthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->singleWidthBadge  . 'px;';
			} elseif ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->widthBadge  . 'px;';
			}
			if ( isset( $badge->singleFontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->singleFontSizeText . 'px;';
			} elseif ( isset( $badge->fontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->fontSizeText  . 'px;';
			}
			if ( isset( $badge->singleFontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->singleFontWeightLabel . ';';
			} elseif ( isset( $badge->fontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->fontWeightLabel  . ';';
			}
			if ( isset( $badge->singleLineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->singleLineHeightText . 'px;';
			} elseif ( isset( $badge->lineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->lineHeightText  . 'px;';
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.related .asnp-esb-badge-'. absint( $badge->id ) .' {';
			if ( isset( $badge->heightBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->heightBadge . 'px;';
			}
			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->widthBadge . 'px;';
			}
			if ( isset( $badge->fontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->fontSizeText . 'px;';
			}
			if ( isset( $badge->fontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->fontWeightLabel . ';';
			}
			if ( isset( $badge->lineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->lineHeightText . 'px;';
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.asnp-esb-badge-'. absint( $badge->id ) .'::after {';
			$dynamic_styles .= ' width: 20px;';
			$dynamic_styles .= ' height: 100%;';
			$dynamic_styles .= ' bottom: 0px;';
			$dynamic_styles .= ' content: \'\';';
			$dynamic_styles .= ' position: absolute;';

			if ( isset( $badge->badgePositionX ) ) {
				$dynamic_styles .= ' right: ' . ( $badge->badgePositionX == 'right' ? 'auto' : '-10px' ) . ';';
				$dynamic_styles .= ' left: ' . ( $badge->badgePositionX == 'right' ? '-10px' : '' ) . ';';
				$dynamic_styles .= ' border-radius: ' . ( $badge->badgePositionX == 'right' ? '3px 0px 0px 3px' : '0 3px 3px 0' ) . ';';
			}

			if ( isset( $badge->badgeColor ) ) {
				$dynamic_styles .= ' background: ' . $badge->badgeColor . ';';
			}
			$dynamic_styles .= ' transform: ' . ( isset( $badge->badgePositionX ) && $badge->badgePositionX == 'right' ? 'skew( -1055deg )' : 'skew( -15deg )' ) . ';';
			$dynamic_styles .= '}';
		break;

		case 'badge4':
			$dynamic_styles .= '.asnp-esb-productBadge-'. absint( $badge->id ) .' {';
			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $width_cont_badge . ';';
			}
			if ( isset( $badge->heightBadge ) ) {
				$dynamic_styles .= ' height: ' . $height_cont_badge . ';';
			}
			if ( isset( $badge->badgePositionTop ) ) {
				$dynamic_styles .= ' inset: ' . $inset_property . ';';
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.asnp-esb-badge-'. absint( $badge->id ) .' {';
			$dynamic_styles .= ' text-align: center;';
			$dynamic_styles .= ' text-shadow: none;';
			if ( isset( $badge->badgeColor ) ) {
				$dynamic_styles .= ' background: ' . $badge->badgeColor . ';';
			}
			if ( isset( $badge->heightBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->heightBadge . 'px;';
			}
			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->widthBadge . 'px;';
			}
			if ( isset( $badge->badgePositionX ) ) {
				$dynamic_styles .= ' left: ' . ( $badge->badgePositionX == 'right' ? 'auto' : '0px' ) . ';';
			}
			if ( isset( $badge->badgePositionX ) && 'right' === $badge->badgePositionX ) {
				$dynamic_styles .= ' right: 0px;';
			}
			if ( isset( $badge->fontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->fontSizeText . 'px;';
			}
			if ( isset( $badge->fontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->fontWeightLabel . ';';
			}
			if ( isset( $badge->textColor ) ) {
				$dynamic_styles .= ' color: ' . $badge->textColor . ';';
			}
			if ( isset( $badge->lineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->lineHeightText . 'px;';
			}
			if ( isset( $badge->opacity ) ) {
				$dynamic_styles .= ' opacity: ' . $badge->opacity . ';';
			}
			if ( isset( $badge->topLeftRadius ) ) {
				$dynamic_styles .= ' border-top-left-radius: ' . $badge->topLeftRadius . 'px;';
			}
			if ( isset( $badge->topRightRadius ) ) {
				$dynamic_styles .= ' border-top-right-radius: ' . $badge->topRightRadius . 'px;';
			}
			if ( isset( $badge->bottomLeftRadius ) ) {
				$dynamic_styles .= ' border-bottom-left-radius: ' . $badge->bottomLeftRadius . 'px;';
			}
			if ( isset( $badge->bottomRightRadius ) ) {
				$dynamic_styles .= ' border-bottom-right-radius: ' . $badge->bottomRightRadius . 'px;';
			}
			if ( isset( $badge->zIndex ) ) {
				$dynamic_styles .= ' z-index: ' . $badge->zIndex . ';';
			}
			$dynamic_styles .= ' display: inline-block;';
			$dynamic_styles .= ' padding: 0px 15px;';
			$dynamic_styles .= ' top: 0px;';
			$dynamic_styles .= ' box-sizing: border-box;';
			$transform = '';
			if ( isset( $badge->rotationX ) ) {
				$transform .= ' rotateX(' . esc_html( $badge->rotationX ) . 'deg)';
			}
			if ( isset( $badge->rotationY ) ) {
				$transform .= ' rotateY(' . esc_html( $badge->rotationY ) . 'deg) ';
			}
			if ( isset( $badge->rotationZ ) ) {
				$transform .= ' rotateZ(' . esc_html( $badge->rotationZ ) . 'deg);';
			}
			if ( ! empty( $transform ) ) {
				$dynamic_styles .= ' transform:' . $transform;
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.single .asnp-esb-badge-'. absint( $badge->id ) .' {';
			if ( isset( $badge->singleHeightBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->singleHeightBadge . 'px;';
			} elseif ( isset( $badge->heightBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->heightBadge  . 'px;';
			}
			if ( isset( $badge->singleWidthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->singleWidthBadge  . 'px;';
			} elseif ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->widthBadge  . 'px;';
			}
			if ( isset( $badge->singleFontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->singleFontSizeText . 'px;';
			} elseif ( isset( $badge->fontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->fontSizeText  . 'px;';
			}
			if ( isset( $badge->singleFontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->singleFontWeightLabel . ';';
			} elseif ( isset( $badge->fontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->fontWeightLabel  . ';';
			}
			if ( isset( $badge->singleLineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->singleLineHeightText . 'px;';
			} elseif ( isset( $badge->lineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->lineHeightText  . 'px;';
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.related .asnp-esb-badge-'. absint( $badge->id ) .' {';
			if ( isset( $badge->heightBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->heightBadge . 'px;';
			}
			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->widthBadge . 'px;';
			}
			if ( isset( $badge->fontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->fontSizeText . 'px;';
			}
			if ( isset( $badge->fontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->fontWeightLabel . ';';
			}
			if ( isset( $badge->lineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->lineHeightText . 'px;';
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.asnp-esb-badge-'. absint( $badge->id ) .'::after {';
			$dynamic_styles .= ' content: \'\';';
			$dynamic_styles .= ' position: absolute;';
			$dynamic_styles .= ' display: block;';
			$dynamic_styles .= ' top: calc( 100% / 2 - 4px );';
			$dynamic_styles .= ' background: #ffffff;';
			$dynamic_styles .= ' width: 7px;';
			$dynamic_styles .= ' height: 7px;';
			$dynamic_styles .= ' border-radius: 10px;';
			if ( isset( $badge->badgePositionX ) ) {
				$dynamic_styles .= ' right: ' . ( $badge->badgePositionX == 'right' ? 'auto' : '0px' ) . ';';
				$dynamic_styles .= ' left: ' . ( $badge->badgePositionX == 'right' ? '0px' : '' ) . ';';
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.asnp-esb-badge-'. absint( $badge->id ) .'::before {';
			$dynamic_styles .= ' top: 0px;';
			$dynamic_styles .= ' display: block;';
			$dynamic_styles .= ' width: 0;';
			$dynamic_styles .= ' height: 0;';
			$dynamic_styles .= ' border-top: 15px solid transparent;';
			$dynamic_styles .= ' border-bottom: 15px solid transparent;';
			$dynamic_styles .= ' content: \'\';';
			$dynamic_styles .= ' position: absolute;';
			if ( isset( $badge->badgePositionX ) ) {
				$dynamic_styles .= ' right: ' . ( $badge->badgePositionX == 'right' ? 'auto' : '-14px' ) . ';';
				$dynamic_styles .= ' left: ' . ( $badge->badgePositionX == 'right' ? '-14px' : '' ) . ';';
				$dynamic_styles .= ' border-top-left-radius: ' . ( $badge->badgePositionX == 'right' ? '0px' : '1px' ) . ';';
				$dynamic_styles .= ' border-bottom-left-radius: ' . ( $badge->badgePositionX == 'right' ? '0px' : '1px' ) . ';';
				$dynamic_styles .= ' border-top-right-radius: ' . ( $badge->badgePositionX == 'right' ? '1px' : '' ) . ';';
				$dynamic_styles .= ' border-bottom-right-radius: ' . ( $badge->badgePositionX == 'right' ? '1px' : '' ) . ';';
				$dynamic_styles .= ' border-left: ' . ( $badge->badgePositionX == 'right' ? 'none' : '15px solid ' . $badge->badgeColor . '' ) . ';';
				$dynamic_styles .= ' border-right: ' . ( $badge->badgePositionX == 'right' ? '15px solid ' . $badge->badgeColor . '' : 'none' ) . ';';
			}
			$dynamic_styles .= '}';
		break;

		case 'badge5':
			$dynamic_styles .= '.asnp-esb-productBadge-'. absint( $badge->id ) .' {';
			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $width_cont_badge . ';';
			}
			if ( isset( $badge->heightBadge ) ) {
				$dynamic_styles .= ' height: ' . $height_cont_badge . ';';
			}
			if ( isset( $badge->badgePositionTop ) ) {
				$dynamic_styles .= ' inset: ' . $inset_property . ';';
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.asnp-esb-badge-'. absint( $badge->id ) .' {';
			$dynamic_styles .= ' text-align: center;';
			$dynamic_styles .= ' text-shadow: none;';
			$dynamic_styles .= ' display: block;';
			$dynamic_styles .= ' overflow: hidden;';
			$dynamic_styles .= ' z-index: 10;';

			if ( isset( $badge->heightBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->widthBadge . 'px;';
			}
			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->widthBadge . 'px;';
			}
			if ( isset( $badge->fontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->fontSizeText . 'px;';
			}
			if ( isset( $badge->fontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->fontWeightLabel . ';';
			}
			if ( isset( $badge->textColor ) ) {
				$dynamic_styles .= ' color: ' . $badge->textColor . ';';
			}
			if ( isset( $badge->lineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->lineHeightText . 'px;';
			}
			if ( isset( $badge->opacity ) ) {
				$dynamic_styles .= ' opacity: ' . $badge->opacity . ';';
			}
			if ( isset( $badge->zIndex ) ) {
				$dynamic_styles .= ' z-index: ' . $badge->zIndex . ';';
			}

			$transform = '';

			if ( isset( $badge->rotationX ) ) {
				$transform .= ' rotateX(' . esc_html( $badge->rotationX ) . 'deg)';
			}
			if ( isset( $badge->rotationY ) ) {
				$transform .= ' rotateY(' . esc_html( $badge->rotationY ) . 'deg) ';
			}
			if ( ( isset( $badge->badgePositionY ) && $badge->badgePositionY == 'bottom' ) && ( isset( $badge->badgePositionX ) && $badge->badgePositionX == 'left' ) ) {
				$transform .= ' rotateZ(270deg) !important';
			} elseif( ( isset( $badge->badgePositionY ) && $badge->badgePositionY == 'bottom' ) && ( isset( $badge->badgePositionX ) && $badge->badgePositionX == 'right' ) ){
				$transform .= ' rotateZ(90deg) !important';
			}

			if ( ! empty( $transform ) ) {
				$dynamic_styles .= ' transform:' . $transform;
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.single .asnp-esb-badge-'. absint( $badge->id ) .' {';
			if ( isset( $badge->singleWidthBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->singleWidthBadge . 'px;';
			} elseif ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->widthBadge  . 'px;';
			}
			if ( isset( $badge->singleWidthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->singleWidthBadge  . 'px;';
			} elseif ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->widthBadge  . 'px;';
			}
			if ( isset( $badge->singleFontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->singleFontSizeText . 'px;';
			} elseif ( isset( $badge->fontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->fontSizeText  . 'px;';
			}
			if ( isset( $badge->singleFontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->singleFontWeightLabel . ';';
			} elseif ( isset( $badge->fontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->fontWeightLabel  . ';';
			}
			if ( isset( $badge->singleLineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->singleLineHeightText . 'px;';
			} elseif ( isset( $badge->lineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->lineHeightText  . 'px;';
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.related .asnp-esb-badge-'. absint( $badge->id ) .' {';
			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->widthBadge . 'px;';
			}
			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->widthBadge . 'px;';
			}
			if ( isset( $badge->fontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->fontSizeText . 'px;';
			}
			if ( isset( $badge->fontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->fontWeightLabel . ';';
			}
			if ( isset( $badge->lineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->lineHeightText . 'px;';
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.asnp-esb-inner-span1-'. absint( $badge->id ) .' {';
			$dynamic_styles .= ' position: absolute;';
			$dynamic_styles .= ' text-align: center;';
			$dynamic_styles .= ' z-index: 12;';
			if ( isset( $badge->badgeColor ) ) {
				$dynamic_styles .= ' background: ' . $badge->badgeColor . ';';
			}

			if ( isset( $badge->badgePositionX ) ) {
				$dynamic_styles .= ' transform: ' . ( $badge->badgePositionX == 'right' ? 'rotate(45deg)' : 'rotate(315deg)' ) . ';';
				$dynamic_styles .= ' left: ' . ( $badge->badgePositionX == 'right' ? '' : '-'. $badge->widthBadge / 2.4  .'px' ) . ';';
				$dynamic_styles .= ' right: ' . ( $badge->badgePositionX == 'right' ? '-'. $badge->widthBadge / 2.22  .'px' : '' ) . ';';
			}

			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->widthBadge * 1.5 . 'px;';
				$dynamic_styles .= ' top: ' . $badge->widthBadge / 7 . 'px;';
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.single .asnp-esb-inner-span1-'. absint( $badge->id ) .' {';
			if ( isset( $badge->singleWidthBadge ) ) {
				$dynamic_styles .= ' left: ' . ( $badge->badgePositionX == 'right' ? '' : '-'. $badge->singleWidthBadge / 2.4  .'px' ) . ';';
				$dynamic_styles .= ' right: ' . ( $badge->badgePositionX == 'right' ? '-'. $badge->singleWidthBadge / 2.22  .'px' : '' ) . ';';
			} elseif ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' left: ' . ( $badge->badgePositionX == 'right' ? '' : '-'. $badge->widthBadge / 2.4  .'px' ) . ';';
				$dynamic_styles .= ' right: ' . ( $badge->badgePositionX == 'right' ? '-'. $badge->widthBadge / 2.22  .'px' : '' ) . ';';
			}
			if ( isset( $badge->singleWidthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->singleWidthBadge * 1.5 . 'px;';
				$dynamic_styles .= ' top: ' . $badge->singleWidthBadge / 7 . 'px;';
			} elseif ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->widthBadge * 1.5 . 'px;';
				$dynamic_styles .= ' top: ' . $badge->widthBadge / 7 . 'px;';
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.related .asnp-esb-inner-span1-'. absint( $badge->id ) .' {';
			if ( isset( $badge->badgePositionX ) ) {
				$dynamic_styles .= ' transform: ' . ( $badge->badgePositionX == 'right' ? 'rotate(45deg)' : 'rotate(315deg)' ) . ';';
				$dynamic_styles .= ' left: ' . ( $badge->badgePositionX == 'right' ? '' : '-'. $badge->widthBadge / 2.4  .'px' ) . ';';
				$dynamic_styles .= ' right: ' . ( $badge->badgePositionX == 'right' ? '-'. $badge->widthBadge / 2.22  .'px' : '' ) . ';';
			}
			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->widthBadge * 1.5 . 'px;';
				$dynamic_styles .= ' top: ' . $badge->widthBadge / 7 . 'px;';
			}
			$dynamic_styles .= '}';
		break;

		case 'badge6':
			$dynamic_styles .= '.asnp-esb-productBadge-'. absint( $badge->id ) .' {';
			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $width_cont_badge . ';';
			}
			if ( isset( $badge->heightBadge ) ) {
				$dynamic_styles .= ' height: ' . $height_cont_badge . ';';
			}
			if ( isset( $badge->badgePositionTop ) ) {
				$dynamic_styles .= ' inset: ' . $inset_property . ';';
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.asnp-esb-badge-'. absint( $badge->id ) .' {';
			$dynamic_styles .= ' text-align: center;';
			$dynamic_styles .= ' text-shadow: none;';
			$dynamic_styles .= ' margin: 0;';
			$dynamic_styles .= ' padding: 0;';
			$dynamic_styles .= ' top: 0px;';
			$dynamic_styles .= ' border-radius: 0;';
			$dynamic_styles .= ' box-sizing: border-box;';

			if ( isset( $badge->heightBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->widthBadge . 'px;';
			}
			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->widthBadge . 'px;';
			}
			if ( isset( $badge->fontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->fontSizeText . 'px;';
			}
			if ( isset( $badge->fontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->fontWeightLabel . ';';
			}
			if ( isset( $badge->textColor ) ) {
				$dynamic_styles .= ' color: ' . $badge->textColor . ';';
			}
			if ( isset( $badge->lineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->lineHeightText . 'px;';
			}
			if ( isset( $badge->opacity ) ) {
				$dynamic_styles .= ' opacity: ' . $badge->opacity . ';';
			}
			if ( isset( $badge->zIndex ) ) {
				$dynamic_styles .= ' z-index: ' . $badge->zIndex . ';';
			}
			if ( isset( $badge->badgePositionX ) ) {
				$dynamic_styles .= ' right: ' . ( $badge->badgePositionX == 'right' ? '0px' : 'auto' ) . ';';
				$dynamic_styles .= ' left: ' . ( $badge->badgePositionX == 'right' ? 'auto' : '0px' ) . ';';
			}

			$transform = '';

			if ( isset( $badge->rotationX ) ) {
				$transform .= ' rotateX(' . esc_html( $badge->rotationX ) . 'deg)';
			}
			if ( isset( $badge->rotationY ) ) {
				$transform .= ' rotateY(' . esc_html( $badge->rotationY ) . 'deg) ';
			}
			if ( ( isset( $badge->badgePositionY ) && $badge->badgePositionY == 'bottom' ) && isset( $badge->badgePositionX ) && $badge->badgePositionX == 'left' ) {
				$transform .= ' rotateZ(270deg) !important';
			} elseif( isset( $badge->badgePositionY ) && $badge->badgePositionY == 'bottom' && isset( $badge->badgePositionX ) && $badge->badgePositionX == 'right' ) {
				$transform .= ' rotateZ(90deg) !important';
			}

			if ( ! empty( $transform ) ) {
				$dynamic_styles .= ' transform:' . $transform;
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.single .asnp-esb-badge-'. absint( $badge->id ) .' {';
			if ( isset( $badge->singleWidthBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->singleWidthBadge . 'px;';
			} elseif ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->widthBadge  . 'px;';
			}
			if ( isset( $badge->singleWidthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->singleWidthBadge  . 'px;';
			} elseif ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->widthBadge  . 'px;';
			}
			if ( isset( $badge->singleFontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->singleFontSizeText . 'px;';
			} elseif ( isset( $badge->fontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->fontSizeText  . 'px;';
			}
			if ( isset( $badge->singleFontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->singleFontWeightLabel . ';';
			} elseif ( isset( $badge->fontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->fontWeightLabel  . ';';
			}
			if ( isset( $badge->singleLineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->singleLineHeightText . 'px;';
			} elseif ( isset( $badge->lineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->lineHeightText  . 'px;';
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.related .asnp-esb-badge-'. absint( $badge->id ) .' {';
			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->widthBadge . 'px;';
			}
			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->widthBadge . 'px;';
			}
			if ( isset( $badge->fontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->fontSizeText . 'px;';
			}
			if ( isset( $badge->fontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->fontWeightLabel . ';';
			}
			if ( isset( $badge->lineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->lineHeightText . 'px;';
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.asnp-esb-inner-span2-'. absint( $badge->id ) .' {';
			$dynamic_styles .= ' width: 0;';
			$dynamic_styles .= ' height: 0;';
			$dynamic_styles .= ' display: block;';
			$dynamic_styles .= ' z-index: 12;';

			if ( isset( $badge->badgePositionX ) ) {
				$dynamic_styles .= ' border-right: ' . ( $badge->badgePositionX == 'right' ? '' . $badge->widthBadge .'px solid '. $badge->badgeColor . '' : 'none' ) . ';';
				$dynamic_styles .= ' border-left: ' . ( $badge->badgePositionX == 'right' ? '' : '' . $badge->widthBadge .'px solid '. $badge->badgeColor . '' ) . ';';
			}

			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' border-bottom: ' . $badge->widthBadge . 'px solid transparent;';
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.single .asnp-esb-inner-span2-'. absint( $badge->id ) .' {';
			if ( isset( $badge->singleWidthBadge ) ) {
				$dynamic_styles .= ' border-right: ' . ( $badge->badgePositionX == 'right' ? '' . $badge->singleWidthBadge .'px solid '. $badge->badgeColor . '' : 'none' ) . ';';
				$dynamic_styles .= ' border-left: ' . ( $badge->badgePositionX == 'right' ? '' : '' . $badge->singleWidthBadge .'px solid '. $badge->badgeColor . '' ) . ';';
			} elseif ( isset( $badge->badgePositionX ) ) {
				$dynamic_styles .= ' border-right: ' . ( $badge->badgePositionX == 'right' ? '' . $badge->widthBadge .'px solid '. $badge->badgeColor . '' : 'none' ) . ';';
				$dynamic_styles .= ' border-left: ' . ( $badge->badgePositionX == 'right' ? '' : '' . $badge->widthBadge .'px solid '. $badge->badgeColor . '' ) . ';';
			}

			if ( isset( $badge->singleWidthBadge ) ) {
				$dynamic_styles .= ' border-bottom: ' . $badge->singleWidthBadge . 'px solid transparent;';
			} elseif ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' border-bottom: ' . $badge->widthBadge . 'px solid transparent;';
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.related .asnp-esb-inner-span2-'. absint( $badge->id ) .' {';
			if ( isset( $badge->badgePositionX ) ) {
				$dynamic_styles .= ' border-right: ' . ( $badge->badgePositionX == 'right' ? '' . $badge->widthBadge .'px solid '. $badge->badgeColor . '' : 'none' ) . ';';
				$dynamic_styles .= ' border-left: ' . ( $badge->badgePositionX == 'right' ? '' : '' . $badge->widthBadge .'px solid '. $badge->badgeColor . '' ) . ';';
			}

			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' border-bottom: ' . $badge->widthBadge . 'px solid transparent;';
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.asnp-esb-inner-span1-'. absint( $badge->id ) .' {';
			$dynamic_styles .= ' position: absolute;';
			$dynamic_styles .= ' z-index: 14;';
			$dynamic_styles .= ' top: 4.02px;';
			$dynamic_styles .= ' text-align: center;';
			$dynamic_styles .= ' display: block;';

			if ( isset( $badge->badgePositionX ) ) {
				$dynamic_styles .= ' transform: ' . ( $badge->badgePositionX == 'right' ? 'rotate(45deg)' : 'rotate(315deg)' ) . ';';
				$dynamic_styles .= ' left: ' . ( $badge->badgePositionX == 'right' ? '0px' : 'auto' ) . ';';
				$dynamic_styles .= ' right: ' . ( $badge->badgePositionX == 'right' ? '' : '0px' ) . ';';
			}

			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->widthBadge * 1.5 . 'px !important;';
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.single .asnp-esb-inner-span1-'. absint( $badge->id ) .' {';
			if ( isset( $badge->singleWidthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->singleWidthBadge * 1.5 . 'px !important;';
			} elseif ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->widthBadge * 1.5 . 'px !important;';
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.related .asnp-esb-inner-span1-'. absint( $badge->id ) .' {';
			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->widthBadge * 1.5 . 'px !important;';
			}
			$dynamic_styles .= '}';

		break;

		case 'badge7':
			$dynamic_styles .= '.asnp-esb-productBadge-'. absint( $badge->id ) .' {';
			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $width_cont_badge . ';';
			}
			if ( isset( $badge->heightBadge ) ) {
				$dynamic_styles .= ' height: ' . $height_cont_badge . ';';
			}
			if ( isset( $badge->badgePositionTop ) ) {
				$dynamic_styles .= ' inset: ' . $inset_property . ';';
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.asnp-esb-badge-'. absint( $badge->id ) .' {';
			$dynamic_styles .= ' text-align: center;';
			$dynamic_styles .= ' text-shadow: none;';
			$dynamic_styles .= ' top: 0px;';
			$dynamic_styles .= ' z-index: 99;';
			$dynamic_styles .= ' border-radius: 3px;;';
			$dynamic_styles .= ' text-align: center;';

			if ( isset( $badge->badgeColor ) ) {
				$dynamic_styles .= ' background: ' . $badge->badgeColor . ';';
			}
			if ( isset( $badge->heightBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->widthBadge . 'px;';
			}
			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->widthBadge . 'px;';
			}
			if ( isset( $badge->fontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->fontSizeText . 'px;';
			}
			if ( isset( $badge->fontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->fontWeightLabel . ';';
			}
			if ( isset( $badge->textColor ) ) {
				$dynamic_styles .= ' color: ' . $badge->textColor . ';';
			}
			if ( isset( $badge->lineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->lineHeightText . 'px;';
			}
			if ( isset( $badge->topLeftRadius ) ) {
				$dynamic_styles .= ' border-top-left-radius: ' . $badge->topLeftRadius . 'px;';
			}
			if ( isset( $badge->topRightRadius ) ) {
				$dynamic_styles .= ' border-top-right-radius: ' . $badge->topRightRadius . 'px;';
			}
			if ( isset( $badge->bottomLeftRadius ) ) {
				$dynamic_styles .= ' border-bottom-left-radius: ' . $badge->bottomLeftRadius . 'px;';
			}
			if ( isset( $badge->bottomRightRadius ) ) {
				$dynamic_styles .= ' border-bottom-right-radius: ' . $badge->bottomRightRadius . 'px;';
			}
			if ( isset( $badge->opacity ) ) {
				$dynamic_styles .= ' opacity: ' . $badge->opacity . ';';
			}
			if ( isset( $badge->zIndex ) ) {
				$dynamic_styles .= ' z-index: ' . $badge->zIndex . ';';
			}
			if ( isset( $badge->badgePositionX ) ) {
				$dynamic_styles .= ' left: ' . ( $badge->badgePositionX == 'right' ? 'auto' : '0px' ) . ';';
				$dynamic_styles .= ' right: ' . ( $badge->badgePositionX == 'right' ? '0px' : '' ) . ';';
			}

			$transform = '';

			if ( isset( $badge->rotationX ) ) {
				$transform .= ' rotateX(' . esc_html( $badge->rotationX ) . 'deg)';
			}

			if ( isset( $badge->rotationY ) ) {
				$transform .= ' rotateY(' . esc_html( $badge->rotationY ) . 'deg) ';
			}

			if ( isset( $badge->rotationZ ) ) {
				$transform .= ' rotateZ(' . esc_html( $badge->rotationZ ) . 'deg);';
			}

			if ( ! empty( $transform ) ) {
				$dynamic_styles .= ' transform:' . $transform;
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.single .asnp-esb-badge-'. absint( $badge->id ) .' {';
			if ( isset( $badge->singleWidthBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->singleWidthBadge . 'px;';
			} elseif ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->widthBadge  . 'px;';
			}
			if ( isset( $badge->singleWidthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->singleWidthBadge  . 'px;';
			} elseif ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->widthBadge  . 'px;';
			}
			if ( isset( $badge->singleFontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->singleFontSizeText . 'px;';
			} elseif ( isset( $badge->fontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->fontSizeText  . 'px;';
			}
			if ( isset( $badge->singleFontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->singleFontWeightLabel . ';';
			} elseif ( isset( $badge->fontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->fontWeightLabel  . ';';
			}
			if ( isset( $badge->singleLineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->singleLineHeightText . 'px;';
			} elseif ( isset( $badge->lineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->lineHeightText  . 'px;';
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.related .asnp-esb-badge-'. absint( $badge->id ) .' {';
			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->widthBadge . 'px;';
			}
			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->widthBadge . 'px;';
			}
			if ( isset( $badge->fontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->fontSizeText . 'px;';
			}
			if ( isset( $badge->fontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->fontWeightLabel . ';';
			}
			if ( isset( $badge->lineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->lineHeightText . 'px;';
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.asnp-esb-inner-span1-'. absint( $badge->id ) .' {';
			$dynamic_styles .= ' transform: translateY(-50%);';
			$dynamic_styles .= ' position: relative;';
			$dynamic_styles .= ' line-height: 16px;';
			$dynamic_styles .= ' top: 50%;';
			$dynamic_styles .= ' z-index: 1;';
			$dynamic_styles .= ' display: block;';
			$dynamic_styles .= '}';
		break;

		case 'badge8':
			$dynamic_styles .= '.asnp-esb-productBadge-'. absint( $badge->id ) .' {';
			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $width_cont_badge . ';';
			}
			if ( isset( $badge->heightBadge ) ) {
				$dynamic_styles .= ' height: ' . $height_cont_badge . ';';
			}
			if ( isset( $badge->badgePositionTop ) ) {
				$dynamic_styles .= ' inset: ' . $inset_property . ';';
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.asnp-esb-badge-'. absint( $badge->id ) .' {';
			$dynamic_styles .= ' text-align: center;';
			$dynamic_styles .= ' text-shadow: none;';
			$dynamic_styles .= ' top: 0px;';
			$dynamic_styles .= ' left: 0px;';
			$dynamic_styles .= ' border-radius: 50% !important;';
			$dynamic_styles .= '  z-index: 99;';

			if ( isset( $badge->badgeColor ) ) {
				$dynamic_styles .= ' background: ' . $badge->badgeColor . ';';
			}
			if ( isset( $badge->heightBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->widthBadge . 'px;';
			}
			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->widthBadge . 'px;';
			}
			if ( isset( $badge->fontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->fontSizeText . 'px;';
			}
			if ( isset( $badge->fontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->fontWeightLabel . ';';
			}
			if ( isset( $badge->textColor ) ) {
				$dynamic_styles .= ' color: ' . $badge->textColor . ';';
			}
			if ( isset( $badge->lineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->lineHeightText . 'px;';
			}
			if ( isset( $badge->opacity ) ) {
				$dynamic_styles .= ' opacity: ' . $badge->opacity . ';';
			}
			if ( isset( $badge->zIndex ) ) {
				$dynamic_styles .= ' z-index: ' . $badge->zIndex . ';';
			}
			if ( isset( $badge->badgePositionX ) ) {
				$dynamic_styles .= ' left: ' . ( $badge->badgePositionX == 'right' ? 'auto' : '0px' ) . ';';
				$dynamic_styles .= ' right: ' . ( $badge->badgePositionX == 'right' ? '0px' : '' ) . ';';
			}

			$transform = '';
			if ( isset( $badge->rotationX ) ) {
				$transform .= ' rotateX(' . esc_html( $badge->rotationX ) . 'deg)';
			}
			if ( isset( $badge->rotationY ) ) {
				$transform .= ' rotateY(' . esc_html( $badge->rotationY ) . 'deg) ';
			}
			if ( isset( $badge->rotationZ ) ) {
				$transform .= ' rotateZ(' . esc_html( $badge->rotationZ ) . 'deg);';
			}
			if ( ! empty( $transform ) ) {
				$dynamic_styles .= ' transform:' . $transform;
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.single .asnp-esb-badge-'. absint( $badge->id ) .' {';
			if ( isset( $badge->singleWidthBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->singleWidthBadge . 'px;';
			} elseif ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->widthBadge  . 'px;';
			}
			if ( isset( $badge->singleWidthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->singleWidthBadge  . 'px;';
			} elseif ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->widthBadge  . 'px;';
			}
			if ( isset( $badge->singleFontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->singleFontSizeText . 'px;';
			} elseif ( isset( $badge->fontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->fontSizeText  . 'px;';
			}
			if ( isset( $badge->singleFontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->singleFontWeightLabel . ';';
			} elseif ( isset( $badge->fontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->fontWeightLabel  . ';';
			}
			if ( isset( $badge->singleLineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->singleLineHeightText . 'px;';
			} elseif ( isset( $badge->lineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->lineHeightText  . 'px;';
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.related .asnp-esb-badge-'. absint( $badge->id ) .' {';
			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->widthBadge . 'px;';
			}
			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->widthBadge . 'px;';
			}
			if ( isset( $badge->fontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->fontSizeText . 'px;';
			}
			if ( isset( $badge->fontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->fontWeightLabel . ';';
			}
			if ( isset( $badge->lineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->lineHeightText . 'px;';
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.asnp-esb-inner-span1-'. absint( $badge->id ) .' {';
			$dynamic_styles .= ' transform: translateY(-50%);';
			$dynamic_styles .= ' position: relative;';
			$dynamic_styles .= ' line-height: 16px;';
			$dynamic_styles .= ' top: 50%;';
			$dynamic_styles .= ' z-index: 1;';
			$dynamic_styles .= ' display: block;';
			$dynamic_styles .= '}';
		break;

		case 'badge9':
			$dynamic_styles .= '.asnp-esb-productBadge-'. absint( $badge->id ) .' {';
			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $width_cont_badge . ';';
			}
			if ( isset( $badge->heightBadge ) && isset( $badge->cssLabelPosition ) && 'onImage' == $badge->cssLabelPosition ) {
				$dynamic_styles .= ' height: ' . $height_cont_badge . ';';
			}
			if ( isset( $badge->badgePositionTop ) ) {
				$dynamic_styles .= ' inset: ' . $inset_property . ';';
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.asnp-esb-badge-'. absint( $badge->id ) .' {';
			$dynamic_styles .= ' text-align: center;';
			$dynamic_styles .= ' text-shadow: none;';
			$dynamic_styles .= ' display: block;';
			$dynamic_styles .= ' z-index: 1;';
			$dynamic_styles .= ' top: 0px;';
			$dynamic_styles .= ' left: 0px;';
			$dynamic_styles .= ' transform: translate3d(0, 0, 0);';
			$dynamic_styles .= '  border-top-right-radius: 3px;';
			$dynamic_styles .= '  border-top-left-radius: 3px;';
			$dynamic_styles .= '  text-align: center;';

			if ( isset( $badge->badgeColor ) ) {
				$dynamic_styles .= ' background: ' . $badge->badgeColor . ';';
			}
			if ( isset( $badge->heightBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->widthBadge / 1.66  . 'px !important;';
			}
			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->widthBadge . 'px;';
			}
			if ( isset( $badge->fontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->fontSizeText . 'px;';
			}
			if ( isset( $badge->fontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->fontWeightLabel . ';';
			}
			if ( isset( $badge->textColor ) ) {
				$dynamic_styles .= ' color: ' . $badge->textColor . ';';
			}
			if ( isset( $badge->lineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->lineHeightText . 'px;';
			}
			if ( isset( $badge->opacity ) ) {
				$dynamic_styles .= ' opacity: ' . $badge->opacity . ';';
			}
			if ( isset( $badge->zIndex ) ) {
				$dynamic_styles .= ' z-index: ' . $badge->zIndex . ';';
			}
			if ( isset( $badge->badgePositionX ) ) {
				$dynamic_styles .= ' left: ' . ( $badge->badgePositionX == 'right' ? 'auto' : '0px' ) . ';';
				$dynamic_styles .= ' right: ' . ( $badge->badgePositionX == 'right' ? '0px' : '' ) . ';';
			}

			$transform = '';

			if ( isset( $badge->rotationX ) ) {
				$transform .= ' rotateX(' . esc_html( $badge->rotationX ) . 'deg)';
			}

			if ( isset( $badge->rotationY ) ) {
				$transform .= ' rotateY(' . esc_html( $badge->rotationY ) . 'deg) ';
			}

			if ( isset( $badge->rotationZ ) ) {
				$transform .= ' rotateZ(' . esc_html( $badge->rotationZ ) . 'deg);';
			}

			if ( ! empty( $transform ) ) {
				$dynamic_styles .= ' transform:' . $transform;
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.single .asnp-esb-badge-'. absint( $badge->id ) .' {';
			if ( isset( $badge->singleWidthBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->singleWidthBadge / 1.66  . 'px !important;';
			} elseif ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->widthBadge / 1.66  . 'px !important;';
			}
			if ( isset( $badge->singleWidthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->singleWidthBadge  . 'px;';
			} elseif ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->widthBadge  . 'px;';
			}
			if ( isset( $badge->singleFontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->singleFontSizeText . 'px;';
			} elseif ( isset( $badge->fontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->fontSizeText  . 'px;';
			}
			if ( isset( $badge->singleFontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->singleFontWeightLabel . ';';
			} elseif ( isset( $badge->fontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->fontWeightLabel  . ';';
			}
			if ( isset( $badge->singleLineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->singleLineHeightText . 'px;';
			} elseif ( isset( $badge->lineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->lineHeightText  . 'px;';
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.related .asnp-esb-badge-'. absint( $badge->id ) .' {';
			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->widthBadge / 1.66  . 'px !important;';
			}
			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->widthBadge . 'px;';
			}
			if ( isset( $badge->fontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->fontSizeText . 'px;';
			}
			if ( isset( $badge->fontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->fontWeightLabel . ';';
			}
			if ( isset( $badge->lineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->lineHeightText . 'px;';
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.asnp-esb-badge-'. absint( $badge->id ) .'::after {';
			$dynamic_styles .= ' content: \'\';';
			$dynamic_styles .= ' position: absolute;';
			$dynamic_styles .= ' height: 0;';
			$dynamic_styles .= ' width: 0;';
			$dynamic_styles .= ' left: 0;';
			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' border-top: ' .   $badge->widthBadge / 4  . 'px solid ' .   $badge->badgeColor  . '!important;';
				$dynamic_styles .= ' border-right: ' .   $badge->widthBadge / 2  . 'px solid transparent !important;';
				$dynamic_styles .= ' border-left: ' .   $badge->widthBadge / 2  . 'px solid transparent !important;';
				$dynamic_styles .= ' top: ' .   $badge->widthBadge / 1.66  . 'px !important;';
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.single .asnp-esb-badge-'. absint( $badge->id ) .'::after {';
			if ( isset( $badge->singleWidthBadge ) ) {
				$dynamic_styles .= ' border-top: ' .   $badge->singleWidthBadge / 4  . 'px solid ' .   $badge->badgeColor  . '!important;';
				$dynamic_styles .= ' border-right: ' .   $badge->singleWidthBadge / 2  . 'px solid transparent !important;';
				$dynamic_styles .= ' border-left: ' .   $badge->singleWidthBadge / 2  . 'px solid transparent !important;';
				$dynamic_styles .= ' top: ' .   $badge->singleWidthBadge / 1.66  . 'px !important;';
			} elseif ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' border-top: ' .   $badge->widthBadge / 4  . 'px solid ' .   $badge->badgeColor  . '!important;';
				$dynamic_styles .= ' border-right: ' .   $badge->widthBadge / 2  . 'px solid transparent !important;';
				$dynamic_styles .= ' border-left: ' .   $badge->widthBadge / 2  . 'px solid transparent !important;';
				$dynamic_styles .= ' top: ' .   $badge->widthBadge / 1.66  . 'px !important;';
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.related .asnp-esb-badge-'. absint( $badge->id ) .'::after {';
			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' border-top: ' .   $badge->widthBadge / 4  . 'px solid ' .   $badge->badgeColor  . '!important;';
				$dynamic_styles .= ' border-right: ' .   $badge->widthBadge / 2  . 'px solid transparent !important;';
				$dynamic_styles .= ' border-left: ' .   $badge->widthBadge / 2  . 'px solid transparent !important;';
				$dynamic_styles .= ' top: ' .   $badge->widthBadge / 1.66  . 'px !important;';
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.asnp-esb-inner-span1-'. absint( $badge->id ) .' {';
			$dynamic_styles .= ' transform: translateY(-50%);';
			$dynamic_styles .= ' position: relative;';
			$dynamic_styles .= ' line-height: 16px;';
			$dynamic_styles .= ' top: 50%;';
			$dynamic_styles .= ' z-index: 1;';
			$dynamic_styles .= ' display: block;';
			$dynamic_styles .= '}';
		break;

		case 'badge10':
			$dynamic_styles .= '.asnp-esb-productBadge-'. absint( $badge->id ) .' {';
			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $width_cont_badge . ';';
			}
			if ( isset( $badge->heightBadge ) && isset( $badge->cssLabelPosition ) && ('onImage' == $badge->cssLabelPosition) ) {
				$dynamic_styles .= ' height: ' . $height_cont_badge . ';';
			}
			if ( isset( $badge->badgePositionTop ) ) {
				$dynamic_styles .= ' inset: ' . $inset_property . ';';
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.asnp-esb-badge-'. absint( $badge->id ) .' {';
			$dynamic_styles .= ' text-align: center;';
			$dynamic_styles .= ' text-shadow: none;';
			$dynamic_styles .= ' display: block;';
			$dynamic_styles .= ' z-index: 1;';
			$dynamic_styles .= ' top: 0px;';
			$dynamic_styles .= ' left: 0px;';
			$dynamic_styles .= ' transform: translate3d(0, 0, 0);';
			$dynamic_styles .= ' border-top-right-radius: 3px;';
			$dynamic_styles .= ' border-top-left-radius: 3px;';
			$dynamic_styles .= ' text-align: center;';

			if ( isset( $badge->badgeColor ) ) {
				$dynamic_styles .= ' background: ' . $badge->badgeColor . ';';
			}
			if ( isset( $badge->heightBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->widthBadge / 1.083  . 'px !important;';
			}
			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->widthBadge . 'px;';
				$dynamic_styles .= ' border-radius: 3px 3px ' .   $badge->widthBadge / 2.38  . 'px ' .   $badge->widthBadge / 2.38  . 'px !important;';
			}
			if ( isset( $badge->fontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->fontSizeText . 'px;';
			}
			if ( isset( $badge->fontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->fontWeightLabel . ';';
			}
			if ( isset( $badge->textColor ) ) {
				$dynamic_styles .= ' color: ' . $badge->textColor . ';';
			}
			if ( isset( $badge->lineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->lineHeightText . 'px;';
			}
			if ( isset( $badge->opacity ) ) {
				$dynamic_styles .= ' opacity: ' . $badge->opacity . ';';
			}
			if ( isset( $badge->zIndex ) ) {
				$dynamic_styles .= ' z-index: ' . $badge->zIndex . ';';
			}

			$transform = '';
			if ( isset( $badge->rotationX ) ) {
				$transform .= ' rotateX(' . esc_html( $badge->rotationX ) . 'deg)';
			}
			if ( isset( $badge->rotationY ) ) {
				$transform .= ' rotateY(' . esc_html( $badge->rotationY ) . 'deg) ';
			}
			if ( isset( $badge->rotationZ ) ) {
				$transform .= ' rotateZ(' . esc_html( $badge->rotationZ ) . 'deg);';
			}
			if ( ! empty( $transform ) ) {
				$dynamic_styles .= ' transform:' . $transform;
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.single .asnp-esb-badge-'. absint( $badge->id ) .' {';
			if ( isset( $badge->singleWidthBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->singleWidthBadge / 1.083  . 'px !important;';
			} elseif ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->widthBadge / 1.083  . 'px !important;';
			}
			if ( isset( $badge->singleWidthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->singleWidthBadge  . 'px;';
				$dynamic_styles .= ' border-radius: 3px 3px ' .   $badge->singleWidthBadge / 2.38  . 'px ' .   $badge->singleWidthBadge / 2.38  . 'px !important;';

			} elseif ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->widthBadge  . 'px;';
				$dynamic_styles .= ' border-radius: 3px 3px ' .   $badge->widthBadge / 2.38  . 'px ' .   $badge->widthBadge / 2.38  . 'px !important;';

			}
			if ( isset( $badge->singleFontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->singleFontSizeText . 'px;';
			} elseif ( isset( $badge->fontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->fontSizeText  . 'px;';
			}
			if ( isset( $badge->singleFontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->singleFontWeightLabel . ';';
			} elseif ( isset( $badge->fontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->fontWeightLabel  . ';';
			}
			if ( isset( $badge->singleLineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->singleLineHeightText . 'px;';
			} elseif ( isset( $badge->lineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->lineHeightText  . 'px;';
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.related .asnp-esb-badge-'. absint( $badge->id ) .' {';
			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->widthBadge / 1.083  . 'px !important;';
			}
			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->widthBadge  . 'px;';
				$dynamic_styles .= ' border-radius: 3px 3px ' .   $badge->widthBadge / 2.38  . 'px ' .   $badge->widthBadge / 2.38  . 'px !important;';
			}
			if ( isset( $badge->fontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->fontSizeText . 'px;';
			}
			if ( isset( $badge->fontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->fontWeightLabel . ';';
			}
			if ( isset( $badge->lineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->lineHeightText . 'px;';
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.asnp-esb-inner-span1-'. absint( $badge->id ) .' {';
			$dynamic_styles .= ' transform: translateY(-50%);';
			$dynamic_styles .= ' position: relative;';
			$dynamic_styles .= ' line-height: 16px;';
			$dynamic_styles .= ' top: 50%;';
			$dynamic_styles .= ' z-index: 1;';
			$dynamic_styles .= ' display: block;';
			$dynamic_styles .= '}';
		break;

		case 'badge14':
			$dynamic_styles .= '.asnp-esb-productBadge-'. absint( $badge->id ) .' {';
			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $width_cont_badge . ';';
			}
			if ( isset( $badge->heightBadge ) ) {
				$dynamic_styles .= ' height: ' . $height_cont_badge . ';';
			}
			if ( isset( $badge->badgePositionTop ) ) {
				$dynamic_styles .= ' inset: ' . $inset_property . ';';
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.asnp-esb-badge-'. absint( $badge->id ) .' {';
			$dynamic_styles .= ' text-align: center;';
			$dynamic_styles .= ' text-shadow: none;';
			$dynamic_styles .= ' top: 0px;';
			$dynamic_styles .= ' left: 0px;';
			$dynamic_styles .= ' border-radius: 61% 60% 63% 53%/66% 31% 92% 34% !important;';
			$dynamic_styles .= ' z-index: 99;';

			if ( isset( $badge->badgeColor ) ) {
				$dynamic_styles .= ' background: ' . $badge->badgeColor . ';';
			}
			if ( isset( $badge->heightBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->widthBadge . 'px;';
			}
			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->widthBadge . 'px;';
			}
			if ( isset( $badge->fontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->fontSizeText . 'px;';
			}
			if ( isset( $badge->fontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->fontWeightLabel . ';';
			}
			if ( isset( $badge->textColor ) ) {
				$dynamic_styles .= ' color: ' . $badge->textColor . ';';
			}
			if ( isset( $badge->borderWidth ) && isset( $badge->borderColor)) {
				$dynamic_styles .= ' border: ' . $badge->borderWidth . 'px solid '. $badge->borderColor .';';
			}
			if ( isset( $badge->lineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->lineHeightText . 'px;';
			}
			if ( isset( $badge->opacity ) ) {
				$dynamic_styles .= ' opacity: ' . $badge->opacity . ';';
			}
			if ( isset( $badge->zIndex ) ) {
				$dynamic_styles .= ' z-index: ' . $badge->zIndex . ';';
			}
			if ( isset( $badge->badgePositionX ) ) {
				$dynamic_styles .= ' left: ' . ( $badge->badgePositionX == 'right' ? 'auto' : '0px' ) . ';';
				$dynamic_styles .= ' right: ' . ( $badge->badgePositionX == 'right' ? '0px' : '' ) . ';';
			}

			$transform = '';
			if ( isset( $badge->rotationX ) ) {
				$transform .= ' rotateX(' . esc_html( $badge->rotationX ) . 'deg)';
			}
			if ( isset( $badge->rotationY ) ) {
				$transform .= ' rotateY(' . esc_html( $badge->rotationY ) . 'deg) ';
			}
			if ( isset( $badge->rotationZ ) ) {
				$transform .= ' rotateZ(' . esc_html( $badge->rotationZ ) . 'deg);';
			}
			if ( ! empty( $transform ) ) {
				$dynamic_styles .= ' transform:' . $transform;
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.single .asnp-esb-badge-'. absint( $badge->id ) .' {';
			if ( isset( $badge->singleWidthBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->singleWidthBadge . 'px;';
			} elseif ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->widthBadge  . 'px;';
			}
			if ( isset( $badge->singleWidthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->singleWidthBadge  . 'px;';
			} elseif ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->widthBadge  . 'px;';
			}
			if ( isset( $badge->singleFontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->singleFontSizeText . 'px;';
			} elseif ( isset( $badge->fontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->fontSizeText  . 'px;';
			}
			if ( isset( $badge->singleFontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->singleFontWeightLabel . ';';
			} elseif ( isset( $badge->fontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->fontWeightLabel  . ';';
			}
			if ( isset( $badge->singleLineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->singleLineHeightText . 'px;';
			} elseif ( isset( $badge->lineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->lineHeightText  . 'px;';
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.related .asnp-esb-badge-'. absint( $badge->id ) .' {';
			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->widthBadge . 'px;';
			}
			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->widthBadge . 'px;';
			}
			if ( isset( $badge->fontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->fontSizeText . 'px;';
			}
			if ( isset( $badge->fontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->fontWeightLabel . ';';
			}
			if ( isset( $badge->lineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->lineHeightText . 'px;';
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.asnp-esb-inner-span1-'. absint( $badge->id ) .' {';
			$dynamic_styles .= ' transform: translateY(-50%);';
			$dynamic_styles .= ' position: relative;';
			$dynamic_styles .= ' line-height: 16px;';
			$dynamic_styles .= ' top: 50%;';
			$dynamic_styles .= ' z-index: 1;';
			$dynamic_styles .= ' display: block;';
			$dynamic_styles .= '}';
		break;

		case 'badge15':
			$dynamic_styles .= '.asnp-esb-productBadge-'. absint( $badge->id ) .' {';
			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $width_cont_badge . ';';
			}
			if ( isset( $badge->heightBadge ) ) {
				$dynamic_styles .= ' height: ' . $height_cont_badge . ';';
			}
			if ( isset( $badge->badgePositionTop ) ) {
				$dynamic_styles .= ' inset: ' . $inset_property . ';';
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.asnp-esb-badge-'. absint( $badge->id ) .' {';
			$dynamic_styles .= ' text-align: center;';
			$dynamic_styles .= ' text-shadow: none;';
			$dynamic_styles .= ' top: 0px;';
			$dynamic_styles .= ' left: 0px;';
			$dynamic_styles .= ' border-radius: 19% 93% 55% 60%/50% 49% 61% 60% !important;';
			$dynamic_styles .= ' z-index: 99;';

			if ( isset( $badge->badgeColor ) ) {
				$dynamic_styles .= ' background: ' . $badge->badgeColor . ';';
			}
			if ( isset( $badge->heightBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->widthBadge . 'px;';
			}
			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->widthBadge . 'px;';
			}
			if ( isset( $badge->fontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->fontSizeText . 'px;';
			}
			if ( isset( $badge->fontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->fontWeightLabel . ';';
			}
			if ( isset( $badge->textColor ) ) {
				$dynamic_styles .= ' color: ' . $badge->textColor . ';';
			}
			if ( isset( $badge->borderWidth ) && isset( $badge->borderColor)) {
				$dynamic_styles .= ' border: ' . $badge->borderWidth . 'px solid '. $badge->borderColor .';';
			}
			if ( isset( $badge->lineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->lineHeightText . 'px;';
			}
			if ( isset( $badge->opacity ) ) {
				$dynamic_styles .= ' opacity: ' . $badge->opacity . ';';
			}
			if ( isset( $badge->zIndex ) ) {
				$dynamic_styles .= ' z-index: ' . $badge->zIndex . ';';
			}
			if ( isset( $badge->badgePositionX ) ) {
				$dynamic_styles .= ' left: ' . ( $badge->badgePositionX == 'right' ? 'auto' : '0px' ) . ';';
				$dynamic_styles .= ' right: ' . ( $badge->badgePositionX == 'right' ? '0px' : '' ) . ';';
			}

			$transform = '';
			if ( isset( $badge->rotationX ) ) {
				$transform .= ' rotateX(' . esc_html( $badge->rotationX ) . 'deg)';
			}
			if ( isset( $badge->rotationY ) ) {
				$transform .= ' rotateY(' . esc_html( $badge->rotationY ) . 'deg) ';
			}
			if ( isset( $badge->rotationZ ) ) {
				$transform .= ' rotateZ(' . esc_html( $badge->rotationZ ) . 'deg);';
			}
			if ( ! empty( $transform ) ) {
				$dynamic_styles .= ' transform:' . $transform;
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.single .asnp-esb-badge-'. absint( $badge->id ) .' {';
			if ( isset( $badge->singleWidthBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->singleWidthBadge . 'px;';
			} elseif ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->widthBadge  . 'px;';
			}
			if ( isset( $badge->singleWidthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->singleWidthBadge  . 'px;';
			} elseif ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->widthBadge  . 'px;';
			}
			if ( isset( $badge->singleFontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->singleFontSizeText . 'px;';
			} elseif ( isset( $badge->fontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->fontSizeText  . 'px;';
			}
			if ( isset( $badge->singleFontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->singleFontWeightLabel . ';';
			} elseif ( isset( $badge->fontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->fontWeightLabel  . ';';
			}
			if ( isset( $badge->singleLineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->singleLineHeightText . 'px;';
			} elseif ( isset( $badge->lineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->lineHeightText  . 'px;';
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.related .asnp-esb-badge-'. absint( $badge->id ) .' {';
			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->widthBadge . 'px;';
			}
			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->widthBadge . 'px;';
			}
			if ( isset( $badge->fontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->fontSizeText . 'px;';
			}
			if ( isset( $badge->fontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->fontWeightLabel . ';';
			}
			if ( isset( $badge->lineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->lineHeightText . 'px;';
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.asnp-esb-inner-span1-'. absint( $badge->id ) .' {';
			$dynamic_styles .= ' transform: translateY(-50%);';
			$dynamic_styles .= ' position: relative;';
			$dynamic_styles .= ' line-height: 16px;';
			$dynamic_styles .= ' top: 50%;';
			$dynamic_styles .= ' z-index: 1;';
			$dynamic_styles .= ' display: block;';
			$dynamic_styles .= '}';
		break;

		case 'badge16':
			$dynamic_styles .= '.asnp-esb-productBadge-'. absint( $badge->id ) .' {';
			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $width_cont_badge . ';';
			}
			if ( isset( $badge->heightBadge ) ) {
				$dynamic_styles .= ' height: ' . $height_cont_badge . ';';
			}
			if ( isset( $badge->badgePositionTop ) ) {
				$dynamic_styles .= ' inset: ' . $inset_property . ';';
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.asnp-esb-badge-'. absint( $badge->id ) .' {';
			$dynamic_styles .= ' text-align: center;';
			$dynamic_styles .= ' text-shadow: none;';
			$dynamic_styles .= ' top: 0px;';
			$dynamic_styles .= ' left: 0px;';
			$dynamic_styles .= ' border-radius: 70% 30% 30% 70% / 60% 40% 60% 40% !important;';
			$dynamic_styles .= '  z-index: 99;';

			if ( isset( $badge->badgeColor ) ) {
				$dynamic_styles .= ' background: ' . $badge->badgeColor . ';';
			}
			if ( isset( $badge->heightBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->widthBadge . 'px;';
			}
			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->widthBadge . 'px;';
			}
			if ( isset( $badge->fontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->fontSizeText . 'px;';
			}
			if ( isset( $badge->fontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->fontWeightLabel . ';';
			}
			if ( isset( $badge->textColor ) ) {
				$dynamic_styles .= ' color: ' . $badge->textColor . ';';
			}
			if ( isset( $badge->borderWidth ) && isset( $badge->borderColor)) {
				$dynamic_styles .= ' border: ' . $badge->borderWidth . 'px solid '. $badge->borderColor .';';
			}
			if ( isset( $badge->lineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->lineHeightText . 'px;';
			}
			if ( isset( $badge->opacity ) ) {
				$dynamic_styles .= ' opacity: ' . $badge->opacity . ';';
			}
			if ( isset( $badge->zIndex ) ) {
				$dynamic_styles .= ' z-index: ' . $badge->zIndex . ';';
			}
			if ( isset( $badge->badgePositionX ) ) {
				$dynamic_styles .= ' left: ' . ( $badge->badgePositionX == 'right' ? 'auto' : '0px' ) . ';';
				$dynamic_styles .= ' right: ' . ( $badge->badgePositionX == 'right' ? '0px' : '' ) . ';';
			}

			$transform = '';
			if ( isset( $badge->rotationX ) ) {
				$transform .= ' rotateX(' . esc_html( $badge->rotationX ) . 'deg)';
			}
			if ( isset( $badge->rotationY ) ) {
				$transform .= ' rotateY(' . esc_html( $badge->rotationY ) . 'deg) ';
			}
			if ( isset( $badge->rotationZ ) ) {
				$transform .= ' rotateZ(' . esc_html( $badge->rotationZ ) . 'deg);';
			}
			if ( ! empty( $transform ) ) {
				$dynamic_styles .= ' transform:' . $transform;
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.single .asnp-esb-badge-'. absint( $badge->id ) .' {';
			if ( isset( $badge->singleWidthBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->singleWidthBadge . 'px;';
			} elseif ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->widthBadge  . 'px;';
			}
			if ( isset( $badge->singleWidthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->singleWidthBadge  . 'px;';
			} elseif ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->widthBadge  . 'px;';
			}
			if ( isset( $badge->singleFontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->singleFontSizeText . 'px;';
			} elseif ( isset( $badge->fontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->fontSizeText  . 'px;';
			}
			if ( isset( $badge->singleFontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->singleFontWeightLabel . ';';
			} elseif ( isset( $badge->fontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->fontWeightLabel  . ';';
			}
			if ( isset( $badge->singleLineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->singleLineHeightText . 'px;';
			} elseif ( isset( $badge->lineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->lineHeightText  . 'px;';
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.related .asnp-esb-badge-'. absint( $badge->id ) .' {';
			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->widthBadge . 'px;';
			}
			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->widthBadge . 'px;';
			}
			if ( isset( $badge->fontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->fontSizeText . 'px;';
			}
			if ( isset( $badge->fontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->fontWeightLabel . ';';
			}
			if ( isset( $badge->lineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->lineHeightText . 'px;';
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.asnp-esb-inner-span1-'. absint( $badge->id ) .' {';
			$dynamic_styles .= ' transform: translateY(-50%);';
			$dynamic_styles .= ' position: relative;';
			$dynamic_styles .= ' line-height: 16px;';
			$dynamic_styles .= ' top: 50%;';
			$dynamic_styles .= ' z-index: 1;';
			$dynamic_styles .= ' display: block;';
			$dynamic_styles .= '}';
		break;

		case 'badge17':
			$dynamic_styles .= '.asnp-esb-productBadge-'. absint( $badge->id ) .' {';
			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $width_cont_badge . ';';
			}
			if ( isset( $badge->heightBadge ) ) {
				$dynamic_styles .= ' height: ' . $height_cont_badge . ';';
			}
			if ( isset( $badge->badgePositionTop ) ) {
				$dynamic_styles .= ' inset: ' . $inset_property . ';';
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.asnp-esb-badge-'. absint( $badge->id ) .' {';
			$dynamic_styles .= ' text-align: center;';
			$dynamic_styles .= ' text-shadow: none;';
			$dynamic_styles .= ' top: 0px;';
			$dynamic_styles .= ' left: 0px;';
			$dynamic_styles .= ' border-radius: 20% 80% 80% 20% / 50% 50% 50% 50% !important;';
			$dynamic_styles .= '  z-index: 99;';

			if ( isset( $badge->badgeColor ) ) {
				$dynamic_styles .= ' background: ' . $badge->badgeColor . ';';
			}
			if ( isset( $badge->heightBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->widthBadge . 'px;';
			}
			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->widthBadge . 'px;';
			}
			if ( isset( $badge->fontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->fontSizeText . 'px;';
			}
			if ( isset( $badge->fontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->fontWeightLabel . ';';
			}
			if ( isset( $badge->textColor ) ) {
				$dynamic_styles .= ' color: ' . $badge->textColor . ';';
			}
			if ( isset( $badge->borderWidth ) && isset( $badge->borderColor)) {
				$dynamic_styles .= ' border: ' . $badge->borderWidth . 'px solid '. $badge->borderColor .';';
			}
			if ( isset( $badge->lineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->lineHeightText . 'px;';
			}
			if ( isset( $badge->opacity ) ) {
				$dynamic_styles .= ' opacity: ' . $badge->opacity . ';';
			}
			if ( isset( $badge->zIndex ) ) {
				$dynamic_styles .= ' z-index: ' . $badge->zIndex . ';';
			}
			if ( isset( $badge->badgePositionX ) ) {
				$dynamic_styles .= ' left: ' . ( $badge->badgePositionX == 'right' ? 'auto' : '0px' ) . ';';
				$dynamic_styles .= ' right: ' . ( $badge->badgePositionX == 'right' ? '0px' : '' ) . ';';
			}

			$transform = '';
			if ( isset( $badge->rotationX ) ) {
				$transform .= ' rotateX(' . esc_html( $badge->rotationX ) . 'deg)';
			}
			if ( isset( $badge->rotationY ) ) {
				$transform .= ' rotateY(' . esc_html( $badge->rotationY ) . 'deg) ';
			}
			if ( isset( $badge->rotationZ ) ) {
				$transform .= ' rotateZ(' . esc_html( $badge->rotationZ ) . 'deg);';
			}
			if ( ! empty( $transform ) ) {
				$dynamic_styles .= ' transform:' . $transform;
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.single .asnp-esb-badge-'. absint( $badge->id ) .' {';
			if ( isset( $badge->singleWidthBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->singleWidthBadge . 'px;';
			} elseif ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->widthBadge  . 'px;';
			}
			if ( isset( $badge->singleWidthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->singleWidthBadge  . 'px;';
			} elseif ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->widthBadge  . 'px;';
			}
			if ( isset( $badge->singleFontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->singleFontSizeText . 'px;';
			} elseif ( isset( $badge->fontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->fontSizeText  . 'px;';
			}
			if ( isset( $badge->singleFontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->singleFontWeightLabel . ';';
			} elseif ( isset( $badge->fontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->fontWeightLabel  . ';';
			}
			if ( isset( $badge->singleLineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->singleLineHeightText . 'px;';
			} elseif ( isset( $badge->lineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->lineHeightText  . 'px;';
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.related .asnp-esb-badge-'. absint( $badge->id ) .' {';
			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->widthBadge . 'px;';
			}
			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->widthBadge . 'px;';
			}
			if ( isset( $badge->fontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->fontSizeText . 'px;';
			}
			if ( isset( $badge->fontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->fontWeightLabel . ';';
			}
			if ( isset( $badge->lineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->lineHeightText . 'px;';
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.asnp-esb-inner-span1-'. absint( $badge->id ) .' {';
			$dynamic_styles .= ' transform: translateY(-50%);';
			$dynamic_styles .= ' position: relative;';
			$dynamic_styles .= ' line-height: 16px;';
			$dynamic_styles .= ' top: 50%;';
			$dynamic_styles .= ' z-index: 1;';
			$dynamic_styles .= ' display: block;';
			$dynamic_styles .= '}';
		break;

		case 'badge18':
			$dynamic_styles .= '.asnp-esb-productBadge-'. absint( $badge->id ) .' {';
			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $width_cont_badge . ';';
			}
			if ( isset( $badge->heightBadge ) ) {
				$dynamic_styles .= ' height: ' . $height_cont_badge . ';';
			}
			if ( isset( $badge->badgePositionTop ) ) {
				$dynamic_styles .= ' inset: ' . $inset_property . ';';
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.asnp-esb-badge-'. absint( $badge->id ) .' {';
			$dynamic_styles .= ' text-align: center;';
			$dynamic_styles .= ' text-shadow: none;';
			$dynamic_styles .= ' top: 0px;';
			$dynamic_styles .= ' left: 0px;';
			$dynamic_styles .= ' border-radius: 50% 60% 60% 60% / 15% 66% 15% 66% !important;';
			$dynamic_styles .= '  z-index: 99;';

			if ( isset( $badge->badgeColor ) ) {
				$dynamic_styles .= ' background: ' . $badge->badgeColor . ';';
			}
			if ( isset( $badge->heightBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->widthBadge . 'px;';
			}
			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->widthBadge . 'px;';
			}
			if ( isset( $badge->fontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->fontSizeText . 'px;';
			}
			if ( isset( $badge->fontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->fontWeightLabel . ';';
			}
			if ( isset( $badge->textColor ) ) {
				$dynamic_styles .= ' color: ' . $badge->textColor . ';';
			}
			if ( isset( $badge->borderWidth ) && isset( $badge->borderColor)) {
				$dynamic_styles .= ' border: ' . $badge->borderWidth . 'px solid '. $badge->borderColor .';';
			}
			if ( isset( $badge->lineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->lineHeightText . 'px;';
			}
			if ( isset( $badge->opacity ) ) {
				$dynamic_styles .= ' opacity: ' . $badge->opacity . ';';
			}
			if ( isset( $badge->zIndex ) ) {
				$dynamic_styles .= ' z-index: ' . $badge->zIndex . ';';
			}
			if ( isset( $badge->badgePositionX ) ) {
				$dynamic_styles .= ' left: ' . ( $badge->badgePositionX == 'right' ? 'auto' : '0px' ) . ';';
				$dynamic_styles .= ' right: ' . ( $badge->badgePositionX == 'right' ? '0px' : '' ) . ';';
			}

			$transform = '';
			if ( isset( $badge->rotationX ) ) {
				$transform .= ' rotateX(' . esc_html( $badge->rotationX ) . 'deg)';
			}
			if ( isset( $badge->rotationY ) ) {
				$transform .= ' rotateY(' . esc_html( $badge->rotationY ) . 'deg) ';
			}
			if ( isset( $badge->rotationZ ) ) {
				$transform .= ' rotateZ(' . esc_html( $badge->rotationZ ) . 'deg);';
			}
			if ( ! empty( $transform ) ) {
				$dynamic_styles .= ' transform:' . $transform;
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.single .asnp-esb-badge-'. absint( $badge->id ) .' {';
			if ( isset( $badge->singleWidthBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->singleWidthBadge . 'px;';
			} elseif ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->widthBadge  . 'px;';
			}
			if ( isset( $badge->singleWidthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->singleWidthBadge  . 'px;';
			} elseif ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->widthBadge  . 'px;';
			}
			if ( isset( $badge->singleFontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->singleFontSizeText . 'px;';
			} elseif ( isset( $badge->fontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->fontSizeText  . 'px;';
			}
			if ( isset( $badge->singleFontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->singleFontWeightLabel . ';';
			} elseif ( isset( $badge->fontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->fontWeightLabel  . ';';
			}
			if ( isset( $badge->singleLineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->singleLineHeightText . 'px;';
			} elseif ( isset( $badge->lineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->lineHeightText  . 'px;';
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.related .asnp-esb-badge-'. absint( $badge->id ) .' {';
			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->widthBadge . 'px;';
			}
			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->widthBadge . 'px;';
			}
			if ( isset( $badge->fontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->fontSizeText . 'px;';
			}
			if ( isset( $badge->fontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->fontWeightLabel . ';';
			}
			if ( isset( $badge->lineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->lineHeightText . 'px;';
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.asnp-esb-inner-span1-'. absint( $badge->id ) .' {';
			$dynamic_styles .= ' transform: translateY(-50%);';
			$dynamic_styles .= ' position: relative;';
			$dynamic_styles .= ' line-height: 16px;';
			$dynamic_styles .= ' top: 50%;';
			$dynamic_styles .= ' z-index: 1;';
			$dynamic_styles .= ' display: block;';
			$dynamic_styles .= '}';
		break;

		case 'badge19':
			$dynamic_styles .= '.asnp-esb-productBadge-'. absint( $badge->id ) .' {';
			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $width_cont_badge . ';';
			}
			if ( isset( $badge->heightBadge ) ) {
				$dynamic_styles .= ' height: ' . $height_cont_badge . ';';
			}
			if ( isset( $badge->badgePositionTop ) ) {
				$dynamic_styles .= ' inset: ' . $inset_property . ';';
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.asnp-esb-badge-'. absint( $badge->id ) .' {';
			$dynamic_styles .= ' text-align: center;';
			$dynamic_styles .= ' text-shadow: none;';
			$dynamic_styles .= ' border-radius: 0;';

			if ( isset( $badge->badgeColor ) && isset( $badge->borderWidth ) ) {
				$dynamic_styles .= ' border-bottom: ' . $badge->borderWidth . 'px solid '. $badge->badgeColor .';';
			}
			if ( isset( $badge->heightBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->heightBadge . 'px;';
			}
			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->widthBadge . 'px;';
			}
			if ( isset( $badge->badgePositionX ) ) {
				$dynamic_styles .= ' left: ' . ( $badge->badgePositionX == 'right' ? 'auto' : '0px' ) . ';';
			}
			if ( isset( $badge->badgePositionX ) && 'right' === $badge->badgePositionX ) {
				$dynamic_styles .= ' right: 0px;';
			}
			if ( isset( $badge->textColor ) ) {
				$dynamic_styles .= ' color: ' . $badge->textColor . ';';
			}
			if ( isset( $badge->fontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->fontSizeText . 'px;';
			}
			if ( isset( $badge->fontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->fontWeightLabel . ';';
			}
			if ( isset( $badge->lineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->lineHeightText . 'px;';
			}
			if ( isset( $badge->opacity ) ) {
				$dynamic_styles .= ' opacity: ' . $badge->opacity . ';';
			}
			if ( isset( $badge->zIndex ) ) {
				$dynamic_styles .= ' z-index: ' . $badge->zIndex . ';';
			}

			$transform = '';
			if ( isset( $badge->rotationX ) ) {
				$transform .= ' rotateX(' . esc_html( $badge->rotationX ) . 'deg)';
			}
			if ( isset( $badge->rotationY ) ) {
				$transform .= ' rotateY(' . esc_html( $badge->rotationY ) . 'deg) ';
			}
			if ( isset( $badge->rotationZ ) ) {
				$transform .= ' rotateZ(' . esc_html( $badge->rotationZ ) . 'deg);';
			}
			if ( ! empty( $transform ) ) {
				$dynamic_styles .= ' transform:' . $transform;
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.single .asnp-esb-badge-'. absint( $badge->id ) .' {';
			if ( isset( $badge->singleHeightBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->singleHeightBadge . 'px;';
			} elseif ( isset( $badge->heightBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->heightBadge  . 'px;';
			}
			if ( isset( $badge->singleWidthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->singleWidthBadge  . 'px;';
			} elseif ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->widthBadge  . 'px;';
			}
			if ( isset( $badge->singleFontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->singleFontSizeText . 'px;';
			} elseif ( isset( $badge->fontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->fontSizeText  . 'px;';
			}
			if ( isset( $badge->singleFontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->singleFontWeightLabel . ';';
			} elseif ( isset( $badge->fontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->fontWeightLabel  . ';';
			}
			if ( isset( $badge->singleLineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->singleLineHeightText . 'px;';
			} elseif ( isset( $badge->lineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->lineHeightText  . 'px;';
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.related .asnp-esb-badge-'. absint( $badge->id ) .' {';
			if ( isset( $badge->heightBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->heightBadge . 'px;';
			}
			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->widthBadge . 'px;';
			}
			if ( isset( $badge->fontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->fontSizeText . 'px;';
			}
			if ( isset( $badge->fontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->fontWeightLabel . ';';
			}
			if ( isset( $badge->lineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->lineHeightText . 'px;';
			}
			$dynamic_styles .= '}';

			break;

		case 'badge20':
			$dynamic_styles .= '.asnp-esb-productBadge-'. absint( $badge->id ) .' {';
			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $width_cont_badge . ';';
			}
			if ( isset( $badge->heightBadge ) ) {
				$dynamic_styles .= ' height: ' . $height_cont_badge . ';';
			}
			if ( isset( $badge->badgePositionTop ) ) {
				$dynamic_styles .= ' inset: ' . $inset_property . ';';
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.asnp-esb-badge-'. absint( $badge->id ) .' {';
			$dynamic_styles .= ' text-align: center;';
			$dynamic_styles .= ' text-shadow: none;';

			if ( isset( $badge->badgeColor ) && isset( $badge->borderWidth ) ) {
				$dynamic_styles .= ' border: ' . $badge->borderWidth . 'px solid '. $badge->badgeColor .';';
			}
			if ( isset( $badge->heightBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->heightBadge . 'px;';
			}
			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->widthBadge . 'px;';
			}
			if ( isset( $badge->badgePositionX ) ) {
				$dynamic_styles .= ' left: ' . ( $badge->badgePositionX == 'right' ? 'auto' : '0px' ) . ';';
			}
			if ( isset( $badge->badgePositionX ) && 'right' === $badge->badgePositionX ) {
				$dynamic_styles .= ' right: 0px;';
			}
			if ( isset( $badge->textColor ) ) {
				$dynamic_styles .= ' color: ' . $badge->textColor . ';';
			}
			if ( isset( $badge->fontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->fontSizeText . 'px;';
			}
			if ( isset( $badge->fontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->fontWeightLabel . ';';
			}
			if ( isset( $badge->lineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->lineHeightText . 'px;';
			}
			if ( isset( $badge->opacity ) ) {
				$dynamic_styles .= ' opacity: ' . $badge->opacity . ';';
			}
			if ( isset( $badge->topLeftRadius ) ) {
				$dynamic_styles .= ' border-top-left-radius: ' . $badge->topLeftRadius . 'px;';
			}
			if ( isset( $badge->topRightRadius ) ) {
				$dynamic_styles .= ' border-top-right-radius: ' . $badge->topRightRadius . 'px;';
			}
			if ( isset( $badge->bottomLeftRadius ) ) {
				$dynamic_styles .= ' border-bottom-left-radius: ' . $badge->bottomLeftRadius . 'px;';
			}
			if ( isset( $badge->bottomRightRadius ) ) {
				$dynamic_styles .= ' border-bottom-right-radius: ' . $badge->bottomRightRadius . 'px;';
			}
			if ( isset( $badge->zIndex ) ) {
				$dynamic_styles .= ' z-index: ' . $badge->zIndex . ';';
			}

			$transform = '';
			if ( isset( $badge->rotationX ) ) {
				$transform .= ' rotateX(' . esc_html( $badge->rotationX ) . 'deg)';
			}
			if ( isset( $badge->rotationY ) ) {
				$transform .= ' rotateY(' . esc_html( $badge->rotationY ) . 'deg) ';
			}
			if ( isset( $badge->rotationZ ) ) {
				$transform .= ' rotateZ(' . esc_html( $badge->rotationZ ) . 'deg);';
			}
			if ( ! empty( $transform ) ) {
				$dynamic_styles .= ' transform:' . $transform;
			}
			$dynamic_styles .= '}';
			
			$dynamic_styles .= '.single .asnp-esb-badge-'. absint( $badge->id ) .' {';
			if ( isset( $badge->singleHeightBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->singleHeightBadge . 'px;';
			} elseif ( isset( $badge->heightBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->heightBadge  . 'px;';
			}
			if ( isset( $badge->singleWidthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->singleWidthBadge  . 'px;';
			} elseif ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->widthBadge  . 'px;';
			}
			if ( isset( $badge->singleFontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->singleFontSizeText . 'px;';
			} elseif ( isset( $badge->fontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->fontSizeText  . 'px;';
			}
			if ( isset( $badge->singleFontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->singleFontWeightLabel . ';';
			} elseif ( isset( $badge->fontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->fontWeightLabel  . ';';
			}
			if ( isset( $badge->singleLineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->singleLineHeightText . 'px;';
			} elseif ( isset( $badge->lineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->lineHeightText  . 'px;';
			}
			$dynamic_styles .= '}';

			$dynamic_styles .= '.related .asnp-esb-badge-'. absint( $badge->id ) .' {';
			if ( isset( $badge->heightBadge ) ) {
				$dynamic_styles .= ' height: ' . $badge->heightBadge . 'px;';
			}
			if ( isset( $badge->widthBadge ) ) {
				$dynamic_styles .= ' width: ' . $badge->widthBadge . 'px;';
			}
			if ( isset( $badge->fontSizeText ) ) {
				$dynamic_styles .= ' font-size: ' . $badge->fontSizeText . 'px;';
			}
			if ( isset( $badge->fontWeightLabel ) ) {
				$dynamic_styles .= ' font-weight: ' . $badge->fontWeightLabel . ';';
			}
			if ( isset( $badge->lineHeightText ) ) {
				$dynamic_styles .= ' line-height: ' . $badge->lineHeightText . 'px;';
			}
			$dynamic_styles .= '}';

			break;
	}

	$extra_data = [
		'hide'              => $hide,
		'inset_property'    => $inset_property,
		'width_cont_badge'  => $width_cont_badge,
		'height_cont_badge' => $height_cont_badge,
		'horiz_and_vert'    => $horiz_and_vert,
	];
	$dynamic_styles = apply_filters( 'asnp_wesb_css_badge_styles', $dynamic_styles, $badge, $extra_data );

	return $dynamic_styles;
}

function get_dynamic_style( $badge, $hide = false, $out_of_image = false ) {
	if ( ! $badge ) {
		return '';
	}

	if ( isset( $badge->imgbadge ) && $badge->imgbadge == 1 ) {
		if ( is_pro_active() ) {
			return \AsanaPlugins\WooCommerce\SaleBadgesPro\Helpers\Badges\image_badge_dynamic_styles( $badge, $hide, $out_of_image );
		}
	} elseif ( isset( $badge->imgbadgeAdv ) && $badge->imgbadgeAdv == 1 ) {
		if ( is_pro_active() ) {
			return \AsanaPlugins\WooCommerce\SaleBadgesPro\Helpers\Badges\image_adv_badge_dynamic_styles( $badge, $hide, $out_of_image );
		}
	} elseif ( isset( $badge->useTimerBadge ) && $badge->useTimerBadge == 1 ) {
		if ( is_pro_active() ) {
			return \AsanaPlugins\WooCommerce\SaleBadgesPro\Helpers\Badges\timer_badge_dynamic_styles( $badge, $hide, $out_of_image );
		}
	} elseif ( ! empty( $badge->badgeStyles ) ) {
		return css_badge_dynamic_styles( $badge, $hide, $out_of_image );
	}

	return '';
}
