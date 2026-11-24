<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Models\AksesProject;
use App\Models\Project;
use App\Models\User;
use App\Services\QuotaService;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Middleware\PermissionMiddleware;

class ProjectController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(PermissionMiddleware::using('lihat-project'), only: ['index']),
            new Middleware(PermissionMiddleware::using(['edit-project', 'tambah-project']), only: ['store_project']),
            new Middleware(PermissionMiddleware::using('edit-aksesproject'), only: ['store_akses']),
        ];
    }
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Project::with('children')
                ->where('status', 'ENABLE')
                ->whereNull('parent_id')
                ->orderBy('name', 'ASC')
                ->get();

            return response()->json($data, 200);
        }

        return view('project.project-list', [
            'judul' => 'Kelola Project',
        ]);
    }

    public function edit_project(Request $request)
    {
        $data = Project::find($request->id);
        return response()->json($data, 200);
    }

    public function edit_subproject(Request $request)
    {
        $data = Project::with('parent')->find($request->id);
        $list = Project::where('id', '!=', $request->id)->get();
        $current = Project::findOrFail($request->id);
        $query = Project::all();

        return response()->json([
            'data'  => $data,
            'list'  => $list,
            'query' => $query,
            'current' => $current->root(),
        ], 200);
    }

    public function store_project(Request $request)
    {
        $rules = [];

        if ($request->hasFile('logo')) {
            $rules['logo'] = 'mimes:jpeg,png,jpg|max:5000';
            $rules['name'] = 'required';
        } else {
            $rules['name'] = 'required';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->messages()->first(),
            ], 405);
        } else {
            $id = $request->id;
            $parent = $request->parent;

            ## SUB PROJECT
            if ($parent != null) {
                Project::updateOrCreate(
                    ['id' => $id],
                    [
                        'name'      => trim(strtoupper($request->input('name'))),
                        'parent_id' => $request->input('parentid'),
                        'status'    => $request->input('status'),
                    ]
                );
            } else {
                ## CEK KUOTA HANYA SAAT CREATE DATA BARU
                if (empty($id)) {
                    $isExceeded = QuotaService::isExceeded('max_projects', fn() => Project::count());
                    if ($isExceeded) {
                        // return back()->with('error', 'Batas maksimal pembuatan Project telah tercapai. Hubungi Super Admin.');
                        return response()->json(['message' => 'Batas maksimal pembuatan Project telah tercapai. Hubungi Super Admin.'], 403);
                    }
                }

                $dataProject = [
                    'name'      => trim(strtoupper($request->input('name'))),
                    'parent_id' => null,
                    'inisial'   => trim(strtoupper($request->input('inisial'))),
                    'status'    => $request->input('status'),
                ];

                ## PROJECT
                if ($request->hasFile('logo')) {
                    // Hapus logo lama jika sedang mode update
                    if (!empty($id)) {
                        $oldProject = Project::find($id);
                        if ($oldProject?->logo && Storage::disk('public')->exists('master/project/' . $oldProject->logo)) {
                            Storage::disk('public')->delete('master/project/' . $oldProject->logo);
                        }
                    }

                    $extension = $request->file('logo')->getClientOriginalExtension();
                    $filename  = date('YmdHis') . '_' . uniqid() . '.' . $extension;

                    Storage::disk('public')->putFileAs('master/project', $request->file('logo'), $filename);

                    $dataProject['logo'] = $filename;
                }

                if (empty($id)) {
                    $lastRootCode = Project::orderByRaw('LENGTH(code) DESC')
                        ->orderBy('code', 'desc')
                        ->first();

                    if (!$lastRootCode || !$lastRootCode->code) {
                        $nextNumber = 100;
                    } else {
                        $nextNumber = (int) $lastRootCode->code + 1;
                    }

                    // Jika kolom 'code' bertipe string/varchar:
                    $nextCode = (string) $nextNumber;

                    $dataProject['code'] = $nextCode;
                }

                Project::updateOrCreate(
                    ['id' => $id],
                    $dataProject
                );
            }

            return response()->json([
                'message' => 'Data Berhasil Disimpan.'
            ], 200);
        }
    }

    public function hapus_project(Request $request)
    {
        $model = Project::find($request->id);
        $model->delete();

        return response()->json([
            'message' => 'Data Berhasil Dihapus.'
        ], 200);
    }

    public function akses_user(Request $request)
    {
        $query = User::whereNull('deleted_at')
            ->where('status', 'ENABLE')
            ->when(!Auth::user()->hasRole('SUPER ADMINISTRATOR'), function ($query) {
                // Sembunyikan user yang memiliki role SA atau Administrator
                $query->whereDoesntHave('roles', function ($q) {
                    $q->whereIn('name', ['SUPER ADMINISTRATOR', 'ADMINISTRATOR']);
                });
            })
            ->orderBy('name', 'ASC');

        // tambahkan filter search di sini
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                ->orWhereHas('roles', function ($sub) use ($search) {
                    $sub->where('name', 'like', '%' . $search . '%');
                });
        }

        // baru lakukan paginate
        $users = $query->paginate(9);
        foreach ($users as $user) {
            $payload = [
                'id'  => $user->id,
                'exp' => time() + 3600
            ];
            $user->token = JWT::encode($payload, config('jwt.secret'), 'HS256');
        }

        // Jika AJAX, kirim partial view
        if ($request->ajax()) {
            return view('project._list-user', compact('users'))->render();
        }

        return view('project.akses-project', [
            'judul' => 'Akses Project Pengguna'
        ], compact('users'));
    }

    public function kelola_aksesproject(Request $request, $token = null)
    {
        try {
            ## Periksa Token
            $userId  = null;
            if ($token) {
                $token   = $request->token;
                $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
                $userId       = $decoded->id;
            }

            $users = User::find($userId);
            // $projects = Project::where('status', 'ENABLE')
            //     ->with('allChildren')
            //     ->whereNull('parent_id')
            //     ->orderBy('name', 'ASC')
            //     ->get();
            $projects = Project::with(['pt', 'children'])
                ->where('status', 'ENABLE')
                ->whereNull('parent_id')
                ->get()
                ->sortBy([
                    ['pt.name', 'asc'],
                    ['name', 'asc'],
                ])
                ->values();

            // Get AksesUser Project
            $akses = AksesProject::where('user_id', $userId)->pluck('project_id');

            ## Jika AJAX, kirim partial view
            if ($request->ajax()) {
                return view('project._listproject', compact('users', 'projects', 'akses', 'userId'))->render();
            }

            return view('project.kelola-aksesuser', [
                'judul' => 'Kelola Akses Project'
            ], compact('users', 'projects', 'akses', 'userId'));
        } catch (\UnexpectedValueException $e) {
            abort(404);
        }
    }

    public function akses_setting(Request $request)
    {
        if ($request->ajax()) {
            $user = User::find($request->iduser);
            $data = Project::where('status', 'ENABLE')
                ->with(['children.children.children.children.children'])
                ->whereNull('parent_id')
                ->orderBy('name', 'ASC')
                ->get();

            // Get AksesUser Project
            $akses = AksesProject::where('user_id', $request->iduser)->get();

            return response()->json([
                'user' => $user,
                'project' => $data,
                'akses' => $akses,
            ], 200);
        }
    }

    public function akses_child(Request $request)
    {
        if ($request->ajax()) {
            $parent = Project::find($request->id);
            $allChildren = $parent->allChildren->toArray();
            return response()->json($allChildren, 200);
        }
    }

    public function store_akses(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'userid' => 'required',
            ],
            [
                'userid.required' => 'Pengguna masih kosong.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->messages()->first(),
            ], 405);
        } else {
            $user = User::findOrFail($request->userid);
            // Ambil semua ID yang dicentang dari form
            $allCheckedIds = $request->input('permissions', []);

            if (!empty($allCheckedIds)) {

                // 2. Cari tahu ID mana saja dari daftar tersebut yang ternyata menjadi 'parent_id' bagi data lain

                $parentIds = Project::whereIn('parent_id', $allCheckedIds)
                    ->pluck('parent_id')
                    ->toArray();

                //  Saring array: Ambil ID yang TIDAK termasuk di dalam daftar $parentIds (hanya mengambil anak terkecil)
                $allCheckedIds = array_diff($allCheckedIds, $parentIds);
            }

            $user->aksesproject()->sync($allCheckedIds);


            return response()->json([
                'message' => 'Akses Project Berhasil Disimpan.'
            ], 200);
        }
    }

    public function akses_project(Request $request)
    {
        if ($request->ajax()) {
            $akses = AksesProject::where('user_id', auth()->user()->id)
                ->whereHas('project', function ($q) {
                    $q->where('status', 'ENABLE');
                })
                ->pluck('project_id');
            return response()->json($akses, 200);
        }
    }

    // Mengurutkan dari child ke parent berdasarkan ID child
    function getPathFromChild($childId)
    {
        $path = [];

        $node = Project::with('Parent')->find($childId);

        while ($node) {
            $path[] = $node;
            $node = $node->parent;
        }

        return collect(array_reverse($path)); // dari parent ke child
    }

    public function lokasi_project(Request $request)
    {
        if ($request->ajax()) {
            $path = $this->getPathFromChild($request->lokasi);
            return response()->json($path, 200);
        }
    }

    public function lokasi_ptko_recursive(Request $request)
    {
        if ($request->ajax()) {
            $project = Project::with('allChildren')->findOrFail($request->project);
            return response()->json([$this->formatTree($project)]);
        }
    }

    protected function formatTree($project)
    {
        return [
            'id'       => $project->id,
            'text'     => $project->name,
            'children' => $project->allChildren->map(function ($child) {
                return $this->formatTree($child);
            })->toArray()
        ];
    }

    public function list_aksesproject(Request $request)
    {
        $user = Auth::user();
        $roles = $user->roles->pluck('id')->toArray();

        if ($request->ajax()) {
            $aksesIds = AksesProject::where('user_id', auth()->user()->id)
                ->whereHas('project', function ($q) {
                    $q->where('status', 'ENABLE');
                })
                ->pluck('project_id');

            # kalau user tidak punya akses, ambil semua project ENABLE
            if ($aksesIds->isEmpty()) {
                # Jika role 7 atau HR pusat maka hanya rootId 1
                $rootId = in_array(7, $roles) ? 1 : $request->parentId;

                # AMBIL ANAK TERKECIL DARI MASING2 ROOTID
                $aksesIds = $this->getLeafIds($rootId);
            }


            $aksesHierarchy = collect($aksesIds)->map(function ($id) {
                $project = Project::with('parent')->find($id);
                return collect($this->getParentToChildHierarchy($project)); // ubah ke collection langsung
            });

            $filtered = $aksesHierarchy->filter(function ($hierarchy) use ($request) {
                $rootId = $hierarchy->first()?->id;
                return in_array($rootId, [$request->parentId]);
            })->values();

            return response()->json($filtered, 200);
        }
    }

    private function getLeafIds($parentId)
    {
        $children = Project::where('parent_id', $parentId)
            ->where('status', 'ENABLE')
            ->get();

        if ($children->isEmpty()) {
            # kalau tidak ada anak, berarti parentId ini sendiri adalah leaf
            return collect([$parentId]);
        }

        # kalau ada anak, cek masing-masing anak
        return $children->flatMap(function ($child) {
            return $this->getLeafIds($child->id);
        });
    }

    function getParentToChildHierarchy($project)
    {
        $hierarchy = [];

        while ($project) {
            array_unshift($hierarchy, $project);
            $project = $project->parent;
        }

        return $hierarchy;
    }

    public function get_lokasi_banksdm(Request $request)
    {
        if ($request->ajax()) {
            $userId = auth()->id();
            $canHO  = auth()->user()->can('banksdm-pemanggilan-ho');
            $canTKO = auth()->user()->can('banksdm-pemanggilan-tko');

            if (!$canHO && !$canTKO) {
                return response()->json([], 200);
            }

            // 1. Ambil data akses project user secara dinamis
            $userProjectIds = DB::table('akses_project')
                ->where('user_id', $userId)
                ->pluck('project_id')
                ->toArray();

            $userProjectIds = array_unique(array_filter($userProjectIds));

            // 2. Logika khusus untuk user dengan akses HO
            if ($canHO) {
                // Skenario A: canHO tetapi TIDAK PUNYA data akses spesifik di tabel pivot (Akses Default)
                if (empty($userProjectIds)) {
                    $allowedIds = Project::where('status', 'ENABLE')
                        ->where('parent_id', 1)
                        ->pluck('id')
                        ->toArray();

                    $rootProjects = Project::where('status', 'ENABLE')
                        ->where('parent_id', 1)
                        ->with('childrenRecursive')
                        ->get(['id', 'name AS text', 'parent_id', 'status']);

                    $result = $rootProjects->map(function ($item) use ($allowedIds) {
                        return $this->transformChildren($item, $allowedIds, true);
                    });

                    return response()->json($result, 200);
                }

                // Skenario B: canHO dan MEMILIKI data akses spesifik di tabel pivot (Hanya tampilkan yang di-assign)
                $bindings = implode(',', array_fill(0, count($userProjectIds), '?'));

                // Cari pohon ID ke atas menggunakan cara 1 (Eloquent + DB::raw) agar root-nya ikut terbawa
                $allowedIds = Project::whereIn('id', function ($query) use ($bindings, $userProjectIds) {
                    $query->select('id')
                        ->from(DB::raw("(
                    WITH RECURSIVE project_tree AS (
                        SELECT id, parent_id FROM project WHERE id IN ($bindings) AND status = 'ENABLE'
                        UNION ALL
                        SELECT p.id, p.parent_id FROM project p
                        INNER JOIN project_tree pt ON p.id = pt.parent_id
                        WHERE p.status = 'ENABLE'
                    )
                    SELECT DISTINCT id FROM project_tree
              ) as sub_tree"))
                        ->setBindings($userProjectIds);
                })->pluck('id')->toArray();

                // Ambil data root teratas yang valid berdasarkan pohon akses yang lolos filter
                $rootProjects = Project::where('status', 'ENABLE')
                    ->whereNull('parent_id')
                    ->whereIn('id', $allowedIds) // Mengunci agar root yang tidak diberi akses tidak ikut muncul
                    ->with('childrenRecursive')
                    ->get(['id', 'name AS text', 'parent_id', 'status']);

                // Transformasi data dengan menyetel parameter $isHO menjadi FALSE 
                // agar fungsi transformChildren menyaring data anak menggunakan array $allowedIds
                $result = $rootProjects->map(function ($item) use ($allowedIds) {
                    return $this->transformChildren($item, $allowedIds, false); // <-- Diubah menjadi false
                })->filter()->values();

                return response()->json($result, 200);
            }

            // 3. Logika untuk user TKO murni
            if (empty($userProjectIds)) {
                return response()->json([], 200);
            }

            // Siapkan penanda parameter (?) sebanyak ID akses
            $bindings = implode(',', array_fill(0, count($userProjectIds), '?'));

            // --- PENERAPAN CARA 1 (Murni Eloquent Project Builder menggunakan DB::raw) ---
            $allowedIds = Project::whereIn('id', function ($query) use ($bindings, $userProjectIds) {
                $query->select('id')
                    ->from(DB::raw("(
                        WITH RECURSIVE project_tree AS (
                            -- Ambil project awal dari akses user
                            SELECT id, parent_id FROM project WHERE id IN ($bindings) AND status = 'ENABLE'
                            UNION ALL
                            -- Tarik semua parent ke atas secara rekursif
                            SELECT p.id, p.parent_id FROM project p
                            INNER JOIN project_tree pt ON p.id = pt.parent_id
                            WHERE p.status = 'ENABLE'
                        )
                        SELECT DISTINCT id FROM project_tree
                  ) as sub_tree"))
                    ->setBindings($userProjectIds); // Ikat parameter ID agar aman
            })->pluck('id')->toArray();
            // -----------------------------------------------------------------------------

            // 4. Tarik data root yang berelasi dengan pohon ID yang lolos sensor
            $rootProjects = Project::where('status', 'ENABLE')
                ->whereNull('parent_id')
                ->whereIn('id', $allowedIds)
                ->with('childrenRecursive')
                ->get(['id', 'name AS text', 'parent_id', 'status']);

            // 5. Ubah format data agar sesuai struktur jsTree
            $result = $rootProjects->map(function ($item) use ($allowedIds) {
                return $this->transformChildren($item, $allowedIds, false);
            })->filter()->values();

            return response()->json($result, 200);
        }
    }

    function transformChildren($node, $allowedIds, $isHO)
    {
        // Struktur wajib untuk jsTree agar otomatis terbuka cabangnya jika ada anak
        $node->state = ['opened' => true];

        if ($node->relationLoaded('childrenRecursive') && $node->childrenRecursive) {

            $node->children = $node->childrenRecursive->map(function ($child) use ($allowedIds, $isHO) {
                // Jika HO tampilkan semua. Jika TKO, hanya tampilkan jika ID anak ada di dalam daftar allowed
                if ($isHO || in_array($child->id, $allowedIds)) {
                    return $this->transformChildren($child, $allowedIds, $isHO);
                }
                return null;
            })->filter()->values();
        } else {
            $node->children = [];
        }

        unset($node->childrenRecursive);
        return $node;
    }


    public function project_all(Request $request)
    {
        if ($request->ajax()) {
            $query = Project::where('status', 'ENABLE')
                ->with('children.children.children.children.children')
                ->whereNull('parent_id')
                ->get(['project.name AS text', 'project.*']);

            return response()->json($query, 200);
        }
    }

    public function tree_kontrak(Request $request)
    {
        $access = auth()->user()->getProjectAccess();
        $projectTree = Project::tree()
            ->with(['pt', 'children'])
            ->whereDepth('<', 2)
            ->where(function ($query) use ($access) {
                $query->whereIn('laravel_cte.id', $access)
                    ->orWhereHas('descendants', function ($q) use ($access) {
                        $q->whereIn('laravel_cte.id', $access);
                    })
                    ->orWhereHas('ancestors', function ($q) use ($access) {
                        $q->whereIn('laravel_cte.id', $access);
                    });
            })
            ->get()
            ->sortBy([
                fn($a, $b) => strcmp($a->pt?->name ?? '', $b->pt?->name ?? ''), // 1. Urutkan nama PT (A-Z)
                fn($a, $b) => strcmp($a->name ?? '', $b->name ?? ''),         // 2. Urutkan nama Project (A-Z)
            ])
            ->toTree();

        $formatted = $this->formatForJsTree($projectTree);

        if ($request->ajax()) {
            return response()->json($formatted);
        }
    }

    public function tree(Request $request)
    {
        $access = auth()->user()->getProjectAccess();
        $projectTree = Project::tree()
            ->with(['pt', 'children'])
            ->whereIn('laravel_cte.id', $access)
            ->orWhereHas('descendants', function ($query) use ($access) {
                $query->whereIn('laravel_cte.id', $access);
            })
            ->orWhereHas('ancestors', function ($query) use ($access) {
                $query->whereIn('laravel_cte.id', $access);
            })
            ->orderBy('name', 'asc')
            ->get()
            ->sortBy([
                fn($a, $b) => strcmp($a->pt?->name ?? '', $b->pt?->name ?? ''), // 1. Urutkan nama PT (A-Z)
                fn($a, $b) => strcmp($a->name ?? '', $b->name ?? ''),         // 2. Urutkan nama Project (A-Z)
            ])
            ->toTree();

        $formatted = $this->formatForJsTree($projectTree);

        if ($request->ajax()) {
            return response()->json($formatted);
        }
    }

    private function formatForJsTree($nodes)
    {
        $result = [];
        foreach ($nodes as $node) {
            $item = [
                'id' => $node->id,
                'text' => $node->text, // sesuaikan dengan field nama project
            ];

            if ($node->children->isNotEmpty()) {
                $item['children'] = $this->formatForJsTree($node->children);
            }

            $result[] = $item;
        }
        return $result;
    }
}
