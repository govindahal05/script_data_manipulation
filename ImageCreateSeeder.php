<?php
use Fdw\Core\Models\Media;

/**
 * Class ImageDeleteSeeder
 */
class ImageCreateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $variant_images = \DB::table('imp_product_images')
            ->get();
        $total_image_copied = 0;
        $start_media_id = Media::count();
        foreach ($variant_images as $image) {

            $img_list = explode("||", $image->image);
            $sku = $image->sku;

            printf("\n=============Sku: $sku for Image: $image->image==================");

            foreach ($img_list as $image_name) {
                printf("\n=============Sku: $sku Processing for Image: $image_name==================");

                $variant = \Fdw\Cart\Models\ProductVariant::where('sku', $sku)->first();
                if (!$variant) {
                    continue;
                }

                $product_images = \Fdw\Cart\Models\ProductImage::where('product_variant_id', '=', $variant->id)->get();
                foreach ($product_images as $product_image) {
                    $media = \Fdw\Core\Models\Media::WithTrashed()->find($product_image->media_id);
                    echo "\n Media for product => $product_image->product_id";
                    if ($media) {
                        echo "\n found $media->id";
                        $media->forceDelete();
                        echo "\n Deleting==> " . public_path("media/$media->id");
                        File::deleteDirectory(public_path("media/$media->id"));
                    }
                    $product_image->forceDelete();
                }

                $path_of_image_to_copy = app_path() . "/storage/tmp/$image_name";

                echo "\n Checking File Exists:" . $path_of_image_to_copy;
                if (!file_exists($path_of_image_to_copy)) {
                    continue;
                }

                echo "\n Found:" . $path_of_image_to_copy;

                //New name of the image.
                $new_file_name = \Carbon\Carbon::now()->timestamp . ".$start_media_id." . $image_name;

                echo "\n Creating File $new_file_name";
                Media::where(['file' => $new_file_name])->delete();

                $new_media = \Assetage::images()->saveFromTempFolder(new Media(), $path_of_image_to_copy, false);

                echo "\n New Media for product => $variant->product_id \n";
                $new_media->update(['deleted_at' => 'NULL']);
                \Fdw\Cart\Models\ProductImage::create([
                    'product_id' => $variant->product_id,
                    'product_variant_id' => $variant->id,
                    'media_id' => $new_media->id
                ]);

                $total_image_copied++;
                $start_media_id++;


            }

        }

        echo "\n Total Image Created: $total_image_copied";
    }

}
