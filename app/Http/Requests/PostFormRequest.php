<?php namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\User;
use App\Post;
use Auth;

class PostFormRequest extends FormRequest {

  public function authorize(){
    return true;
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array
   */
  public function rules()
  {
    return [
      'title' => 'required|unique:post|max:255',
      'title' => array('Regex:/^[A-Za-z0-9 ]+$/'),
      'body' => 'required'
    ];
  }
}
