<?php

namespace App\Http\Controllers;

use App\Models\Dictionary;
use App\Models\Page;

class PageController extends Controller
{
    public function __invoke(?string $slug = null)
    {
        if (is_null($slug)) {
            $currentPage = Page::findByType(Page::PAGE_TYPE_HOME);

            if (!isset($currentPage->id)) {
                abort(404);
            }

            /*$dict = Dictionary::getDictionary([
                Dictionary::DICT_HOME,
                Dictionary::DICT_GOOGLE_MAPS,
            ]);*/

            return view('home', [
                'currentPage' => $currentPage,
            ]);
        }

        $currentPage = Page::findBySlug($slug);

        $viewData = [
            'currentPage' => $currentPage,
        ];

        switch ($currentPage->type) {
            /*case Page::MODULE_ABOUT:
                $dict = Dictionary::getDictionary([
                    Dictionary::DICT_ABOUT,
                    Dictionary::DICT_CONTACT_US_FORM,
                ]);

                $viewData['dictAbout'] = $dict->{Dictionary::DICT_ABOUT};
                $viewData['dictForm'] = $dict->{Dictionary::DICT_CONTACT_US_FORM};
                $viewData['ratedSlider'] = $ratedSlider->getSlidesFromCache();
                break;
            case Page::MODULE_CONTACTS:
                $dict = Dictionary::getDictionary([
                    Dictionary::DICT_CONTACTS,
                    Dictionary::DICT_GOOGLE_MAPS,
                    Dictionary::DICT_CONTACT_US_FORM,
                ]);

                $viewData['dict'] = $dict->{Dictionary::DICT_CONTACTS};
                $viewData['dictMap'] = $dict->{Dictionary::DICT_GOOGLE_MAPS};
                $viewData['dictForm'] = $dict->{Dictionary::DICT_CONTACT_US_FORM};
                break;*/
        }

        return view($currentPage->type == Page::PAGE_TYPE_404 ? 'errors/' . $currentPage->type :  $currentPage->type ?? 'page' , $viewData);
    }
}
