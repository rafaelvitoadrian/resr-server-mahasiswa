<?php

namespace App\Controllers;

use App\Models\MahasiswaModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\RESTful\ResourceController;

class Mahasiswa extends ResourceController
{
    use ResponseTrait;
    protected $mahasiswaModel;
    protected $format    = 'json';

    public function __construct()
    {
        $this->mahasiswaModel = new MahasiswaModel();
    }

    public function index()
    {
        $data = $this->mahasiswaModel->findAll();
        return $this->respond($data, 201);
    }

    public function create()
    {
        $rules = [
            'nama' => 'required',
            'nim' => 'required',
            'gambar' => 'is_image[gambar]'
        ];

        if (!$this->validate($rules)) {
            return $this->fail($this->validator->getErrors());
        }else{

            $gambar = $this->request->getFile('gambar');
            if (!$gambar->isValid()) {
                return $this->fail($gambar->getErrorString());
            }

            $gambar->move('test/api');

            $data = [
                'nama' => $this->request->getVar('nama'),
                'nim' => $this->request->getVar('nim'),
                'gambar' => $gambar->getName()
            ];
            $this->mahasiswaModel->insert($data);
            $response = [
                'status' => 200,
                'error' => null,
                'messages' => 'Berhasil Menambah Data',
                'data' => $data
            ];
            return $this->respondCreated($response);
        }
    }

    public function update($id = null)
    {

        helper([
            'form', 'array'
        ]);

        $rules = [
            'nama' => 'required',
            'nim' => 'required'
        ];

        $fileNama = dot_array_search('gambar.name', $_FILES);

        if ($fileNama != '') {
            $img = ['gambar' => 'is_image[gambar]'];
            $rules = array_merge($rules, $img);
        }
        if (!$this->validate($rules)) {
            return $this->fail($this->validator->getErrors());
        }else{
            // $input = $this->request->getRawInput();


            $data = [
                'id' => $id,
                'nama' => $this->request->getVar('nama'),
                'nim' => $this->request->getVar('nim')
            ];

            if($fileNama != ''){
                $gambar = $this->request->getFile('gambar');
                if (!$gambar->isValid()) {
                    return $this->fail($gambar->getErrorString());
                }

                $gambarLama = $this->mahasiswaModel->where('id', $id)->first();
                $gambarLama = $gambarLama['gambar'];

                $gambar->move('test/api');
                unlink('test/api/'. $gambarLama);
                $data['gambar'] = $gambar->getName();
            }

            $this->mahasiswaModel->save($data);
            return $this->respond($data);
        }
    }

    public function show($id = null)
    {
        $data = $this->mahasiswaModel->where('id', $id)->first();

        if (!$data) {
            return $this->failNotFound('Data tidak ditemukan');
        }else{
            return $this->respond($data, 201);
        }
    }

    public function delete($id = null)
    {
        $data = $this->mahasiswaModel->where('id',$id)->first();
        $gambar = $data['gambar'];
        if (!$data) {
            return $this->fail('data mungkin sudah tidak ada atau sudah diubah');
        }else{
            if (!$gambar) {
                unlink('test/api/'. $gambar);
            }
            $data = $this->mahasiswaModel->delete($id);
            return $this->respondDeleted($data, 201);
        }
    }
}