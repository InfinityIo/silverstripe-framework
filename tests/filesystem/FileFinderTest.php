<?php
/**
 * Tests for the {@link SS_FileFinder} class.
 *
 * @package framework
 * @subpackage tests
 */
class FileFinderTest extends SapphireTest {

	protected $base;

	public function __construct() {
		$this->base = dirname(__FILE__) . '/fixtures/filefinder';
		parent::__construct();
	}

	public function testBasicOperation() {
		$this->assertFinderFinds(new SS_FileFinder(), array(
			'file1.txt',
			'file2.txt',
			'dir1/dir1file1.txt',
			'dir1/dir1file2.txt',
			'dir1/dir2/dir2file1.txt',
			'dir1/dir2/dir3/dir3file1.txt'
		));
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testInvalidOptionThrowsException() {
		$finder = new SS_FileFinder();
		$finder->setOption('this_doesnt_exist', 'ok');
	}

	public function testFilenameRegex() {
		$finder = new SS_FileFinder();
		$finder->setOption('name_regex', '/file2\.txt$/');

		$this->assertFinderFinds(
			$finder,
			array(
				'file2.txt',
				'dir1/dir1file2.txt'),
			'The finder only returns files matching the name regex.');
	}

    public function testVcsDirsFromEnvironment()
    {
        define('SS_FILEFINDER_VCS_DIRS', '.something,.somethingElse');
        // The static is updated in the constructor
        $finder   = new SS_FileFinder();
        $vcs_dirs = new ReflectionProperty('SS_FileFinder', 'vcs_dirs');
        $vcs_dirs->setAccessible(true);
        $this->assertContains('.something', $vcs_dirs->getValue(),
            'The finder adds folders from SS_FILEFINDER_VCS_DIRS to the vcs_dirs setting');
        $this->assertContains('.somethingElse', $vcs_dirs->getValue(),
            'The finder adds folders from SS_FILEFINDER_VCS_DIRS to the vcs_dirs setting');
    }

    public function testIgnoreDirsFromEnvironment()
    {
        define('SS_FILEFINDER_IGNORE_DIRS', 'some.dir,someOther.dir');
        $finder = new SS_FileFinder();
        $options = new ReflectionProperty('SS_FileFinder', 'options');
        $options->setAccessible(true);
        $options = $options->getValue($finder);
        $this->assertContains('some.dir', $options['ignore_dirs'],
            'The finder adds files from SS_FILEFINDER_IGNORE_DIRS to ignore_dirs');
        $this->assertContains('someOther.dir', $options['ignore_dirs'],
            'The finder adds files from SS_FILEFINDER_IGNORE_DIRS to ignore_dirs');
    }

    public function testIgnoreFilesFromEnvironment()
    {
        define('SS_FILEFINDER_IGNORE_FILES', 'some.file,someOther.file');
        $finder = new SS_FileFinder();
        $options = new ReflectionProperty('SS_FileFinder', 'options');
        $options->setAccessible(true);
        $options = $options->getValue($finder);
        $this->assertContains('some.file', $options['ignore_files'],
            'The finder adds files from SS_FILEFINDER_IGNORE_FILES to ignore_files');
        $this->assertContains('someOther.file', $options['ignore_files'],
            'The finder adds files from SS_FILEFINDER_IGNORE_FILES to ignore_files');
    }

	public function testIgnoreFiles() {
		$finder = new SS_FileFinder();
		$finder->setOption('ignore_files', array('file1.txt', 'dir1file1.txt', 'dir2file1.txt'));

		$this->assertFinderFinds(
			$finder,
			array(
				'file2.txt',
				'dir1/dir1file2.txt',
				'dir1/dir2/dir3/dir3file1.txt'),
			'The finder ignores files with the basename in the ignore_files setting.');
	}

	public function testIgnoreDirs() {
		$finder = new SS_FileFinder();
		$finder->setOption('ignore_dirs', array('dir2'));

		$this->assertFinderFinds(
			$finder,
			array(
				'file1.txt',
				'file2.txt',
				'dir1/dir1file1.txt',
				'dir1/dir1file2.txt'),
			'The finder ignores directories in ignore_dirs.');
	}

	public function testMinDepth() {
		$finder = new SS_FileFinder();
		$finder->setOption('min_depth', 2);

		$this->assertFinderFinds(
			$finder,
			array(
				'dir1/dir2/dir2file1.txt',
				'dir1/dir2/dir3/dir3file1.txt'),
			'The finder respects the min depth setting.');
	}

	public function testMaxDepth() {
		$finder = new SS_FileFinder();
		$finder->setOption('max_depth', 1);

		$this->assertFinderFinds(
			$finder,
			array(
				'file1.txt',
				'file2.txt',
				'dir1/dir1file1.txt',
				'dir1/dir1file2.txt'),
			'The finder respects the max depth setting.');
	}

	public function assertFinderFinds($finder, $expect, $message = null) {
		$found = $finder->find($this->base);

		foreach ($expect as $k => $file) {
			$expect[$k] = "{$this->base}/$file";
		}

		sort($expect);
		sort($found);

		$this->assertEquals($expect, $found, $message);
	}

}
