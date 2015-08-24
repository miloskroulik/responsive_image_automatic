<?php

/**
 * @file
 * Contains \Drupal\Tests\responsive_image_automatic\Unit\ResponsiveImageAutomaticTest.
 */

namespace Drupal\Tests\responsive_image_automatic\Unit;

use Drupal\Tests\UnitTestCase;

/**
 * Test the responsive image automatic setup.
 *
 * @group responsive_image_automatic
 */
class ResponsiveImageAutomaticTest extends UnitTestCase {

  /**
   * Test the derivative creation.
   */
  public function testDerivativeCreation() {
    $image_style = $this->getMockImageStyleEntity(1200, 1200);
    $writes = 1;
    $image_style
      ->expects($this->any())
      ->method('writeDerivative')
      ->willReturnCallback(function($original, $new) use (&$writes) {
        switch ($writes) {
          case 1:
            $this->assertEquals($new, 'public://styles/large/upload.jpg');
            break;
          case 2:
            $this->assertEquals($new, 'public://styles/large/upload_800.jpg');
            break;
          case 3:
            $this->assertEquals($new, 'public://styles/large/upload_400.jpg');
            break;
        }
        $writes++;
      });
    $image_style->createDerivative('public://upload.jpg', 'public://styles/large/upload.jpg');
  }

  /**
   *
   */
  public function testDerivativesNotCreatedBiggerThanOriginal() {
    $image_style = $this->getMockImageStyleEntity(2000, 2000);
    $image_style
      ->expects($this->any())
      ->method('writeDerivative')
      ->willReturnCallback(function($original, $new) use (&$writes) {

      });
    $image_style->createDerivative('public://upload_1200x1200.jpg', 'public://target.jpg');
  }

  /**
   * Get the mock image style entity.
   */
  public function getMockImageStyleEntity($resize_width, $resize_height) {
    $resize_effect = $this->getMockBuilder('Drupal\image\Plugin\ImageEffect\ResizeImageEffect')
      ->disableOriginalConstructor()
      ->setMethods(['getConfiguration'])
      ->getMock();
    $resize_effect
      ->expects($this->any())
      ->method('getConfiguration')
      ->willReturn([
        'data' => [
          'width' => $resize_width,
          'height' => $resize_height,
        ],
      ]);

    $file_system = $this->getMockBuilder('Drupal\Core\File\FileSystem')
      ->disableOriginalConstructor()
      ->setMethods(['dirname'])
      ->getMock();

    $file_system
      ->expects($this->any())
      ->method('dirname')
      ->willReturnCallback(function($path) {
        return 'public://styles/large';
      });

    $image_style = $this->getMockBuilder('Drupal\responsive_image_automatic\Entity\ImageStyle')
      ->disableOriginalConstructor()
      ->setMethods(['writeDerivative', 'getResizeEffect', 'getFilesystem'])
      ->getMock();

    $image_style
      ->expects($this->any())
      ->method('getResizeEffect')
      ->willReturn($resize_effect);

    $image_style
      ->expects($this->any())
      ->method('writeDerivative')
      ->willReturn(TRUE);

    $image_style
      ->expects($this->any())
      ->method('getFilesystem')
      ->willReturn($file_system);
    return $image_style;
  }

}