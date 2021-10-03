<?php

namespace App\Repository\Eloquent;

use App\DataKelas;
use App\Kelas;
use App\Questions;
use App\Repository\RepositoryInterface;
use App\User;


class KelasRepository implements RepositoryInterface
{
    private $user;
    private $kls;
    private $dk;
    public function __construct(User $user, Kelas $kls, DataKelas $dk)
    {
        $this->user = $user;
        $this->kls = $kls;
        $this->dk = $dk;
    }

    public function getKelas()
    {
        $kelas = $this->user::where('id', auth()
            ->user()
            ->id)
            ->with('dk')
            ->get();
        $format = self::format($kelas);
        return $format;
    }

    protected function format($kelas)
    {
        $map = $kelas->map(function ($value) {
            $arr = [];
            $arr['user_auth'] = $value->name;
            foreach ($value['dk'] as $key => $dk) {
                $arr['kelas'][$key]['id'] = $dk->id;
                $arr['kelas'][$key]['uuid'] = $dk->kelas->uuid;
                $arr['kelas'][$key]['nama_kelas'] = $dk->kelas->nama_kelas;
                $arr['kelas'][$key]['mapel'] = $dk->kelas->mapel;
                $arr['kelas'][$key]['nama_guru'] = $dk->kelas->user->name;
                $arr['kelas'][$key]['code'] = $dk->kelas->kode_kelas;
                $arr['kelas'][$key]['jumlah'] = $dk->kelas->dk->where('status', 0)->count();
                $arr['kelas'][$key]['photo'] = $dk->kelas->user->photo;
            }
            return $arr;
        });
        return $map;
    }

    public function getKode($kode)
    {
        $kelas = $this->kls::where('kode_kelas', $kode)->first();

        if (!empty($kelas)) {
            $user =  $this->user::find(auth()->user()->id);
            if ($user->dk()->where('kelas_id', $kelas->id)->exists()) {
                return back()->with('info', 'Kode Kelas Sudah Ada!');
            } else {
                DataKelas::Create([
                    'kelas_id' => $kelas->id,
                    'user_id' => $user->id,
                    'status' => '0',
                ]);
                return back()->with('success', 'Kelas is Exist!');
            }
        } else {
            return back()->with('warning', 'Code Kelass Not Worthid');
        }
    }

    public function show($uuid)
    {
        $kelas = $this->kls::where('uuid', $uuid)->with('user')->first();
        if (!empty($kelas)) {
            $user =  $this->user::find(auth()->user()->id);
            if ($user->dk()->where('kelas_id', $kelas->id)->exists()) {
                $questions =  Questions::where('kelas_id', $kelas->id)->with('user')->latest()->paginate(10);
                return view('kelas.kls_show', compact('kelas', 'questions'))->render();
            } else {
                return back()->with('info', 'No Class in Hire');
            }
        }
    }

    public function store($attributed)
    {
        $kelas = $this->kls->create($attributed);
        $user =  $this->user::find(auth()->user()->id);
        DataKelas::Create([
            'kelas_id' => $kelas->id,
            'user_id' => $user->id,
            'status' => '1',
        ]);
    }

    public function delete($uuid)
    {
        $kelas = kelas::where('uuid', $uuid)->firstOrfail();
        $kelas->delete();
    }

    public function dkSiswa($id, $uuid)
    {
        $siswa = DataKelas::where('kelas_id', $id)->get();
        return view('kelas.kls_siswa', compact('siswa', 'uuid'));
    }
}
